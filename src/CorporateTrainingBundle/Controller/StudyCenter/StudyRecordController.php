<?php

namespace CorporateTrainingBundle\Controller\StudyCenter;

use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\BaseController;
use AppBundle\Common\Paginator;

class StudyRecordController extends BaseController
{
    public function recordAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $postCourses = $this->getPostCourseService()->findPostCoursesByPostId($user['postId']);

        if (!empty($postCourses)) {
            $courseIds = ArrayToolkit::column($postCourses, 'courseId');
            $courses = $this->getCourseService()->findCoursesByIds($courseIds);
            list($courses, $totalLearnTime) = $this->calculateLearnTime($user['id'], $courses);
            $learnedCoursesNum = $this->getPostCourseService()->countUserLearnedPostCourses($user['id'], $courseIds);
            $recentStartTask = $this->findRecentStartTask($user['id'], $courseIds);
        }

        return $this->render(
            'study-center/study-record/record.html.twig',
            array(
                'courses' => empty($courses) ? array() : $courses,
                'postCourseCount' => empty($postCourses) ? 0 : count($postCourses),
                'totalLearnTime' => empty($totalLearnTime) ? 0 : $totalLearnTime,
                'learnedCoursesNum' => empty($learnedCoursesNum) ? 0 : $learnedCoursesNum,
                'recentStartTask' => empty($recentStartTask) ? array() : $recentStartTask,
                'user' => $user,
                'tab_types' => $request->get('tab_types', 'projectPlanRecord'),
            )
        );
    }

    public function offlineActivityRecordAction(Request $request, $userId, $source = null)
    {
        $paginator = new Paginator(
            $request,
            $this->getOfflineActivityMemberService()->countMembers(array('userId' => $userId)),
            PHP_INT_MAX
        );
        $members = $this->getOfflineActivityMemberService()->searchMembers(
            array('userId' => $userId),
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $offlineActivityIds = ArrayToolkit::column($members, 'offlineActivityId');
        $offlineActivities = $this->getOfflineActivityService()->findOfflineActivitiesByIds($offlineActivityIds);
        $categoryIds = ArrayToolkit::column($offlineActivities, 'categoryId');
        $categories = $this->getCategoryService()->findCategoriesByIds($categoryIds);
        $offlineActivities = ArrayToolkit::index($offlineActivities, 'id');
        $categories = ArrayToolkit::index($categories, 'id');

        foreach ($members as $key => $member) {
            if (!empty($offlineActivities[$member['offlineActivityId']])) {
                $members[$key]['startDate'] = $offlineActivities[$member['offlineActivityId']]['startDate'];
                $members[$key]['endDate'] = $offlineActivities[$member['offlineActivityId']]['endDate'];
                $members[$key]['title'] = $offlineActivities[$member['offlineActivityId']]['title'];
                if (!empty($categories[$offlineActivities[$member['offlineActivityId']]['categoryId']])) {
                    $members[$key]['categoryName'] = $categories[$offlineActivities[$member['offlineActivityId']]['categoryId']]['name'];
                }
            }
        }
        $view = 'offline-activity/offline-activity-record.html.twig';
        if ($source == 'user_learn') {
            $view = 'data-report/course-statistics/offline-activity-record.html.twig';
        } elseif ($source == 'offline_activity') {
            $view = 'offline-activity-manage/study-record/offline-activity-record.html.twig';
        }

        return $this->render(
            $view,
            array(
                'tab_types' => $request->get('tab_types', 'learningStatistics'),
                'paginator' => $paginator,
                'userId' => $userId,
                'members' => $members,
            )
        );
    }

    public function scoreAction(Request $request, $courseId, $userId, $tab_type)
    {
        $user = $this->getUserService()->getUser($userId);
        if (empty($user)) {
            $user = $this->getCurrentUser();
        }

        list($testPapers, $testPaperResults) = $this->findTestPapersAndResults($courseId, $user['id']);
        list($homeworks, $homeworkResults) = $this->findHomeworkAndResults($courseId, $user['id']);

        return $this->render(
            'study-center/study-record/score.html.twig',
            array(
                'testPaperResults' => empty($testPaperResults) ? array() : $testPaperResults,
                'testPapers' => ArrayToolkit::index($testPapers, 'id'),
                'homeworkResults' => empty($homeworkResults) ? array() : $homeworkResults,
                'homeworks' => ArrayToolkit::index($homeworks, 'id'),
                'tab_type' => $tab_type,
                'userId' => $userId,
            )
        );
    }

    private function findTestPapersAndResults($courseId, $userId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        $testPapers = $this->getTestpaperService()->searchTestpapers(
            array('courseSetId' => $course['courseSetId'], 'status' => 'open', 'type' => 'testpaper'),
            array('createdTime' => 'DESC'),
            0, PHP_INT_MAX
        );

        // $testPapers = ArrayToolkit::index($testPapers, 'id');
        $testPaperResults = array();

        if (!empty($testPapers)) {
            $testPaperResults = $this->getTestpaperService()->searchTestpaperResults(
                array('testIds' => ArrayToolkit::column($testPapers, 'id'), 'userId' => $userId),
                array('id' => 'DESC'),
                0, PHP_INT_MAX
            );
        }

        foreach ($testPaperResults as $key => &$paperResult) {
            $task = $this->getTaskService()->getTaskByActivityId($paperResult['lessonId']);
            if (empty($task)) {
                unset($testPaperResults[$key]);
                continue;
            }
            $paperResult['taskId'] = $task['id'];
        }

        return array($testPapers, $testPaperResults);
    }

    private function findHomeworkAndResults($courseId, $userId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        $homeworks = $this->getTestpaperService()->searchTestpapers(
            array('courseSetId' => $course['courseSetId'], 'status' => 'open', 'type' => 'homework'),
            array('createdTime' => 'DESC'),
            0, PHP_INT_MAX
        );

        // $homeworks = ArrayToolkit::index($homeworks, 'id');
        $homeworkResults = array();

        if (!empty($homeworks)) {
            $homeworkResults = $this->getTestpaperService()->searchTestpaperResults(
                array('testIds' => ArrayToolkit::column($homeworks, 'id'), 'userId' => $userId),
                array('id' => 'DESC'),
                0, PHP_INT_MAX
            );
        }

        foreach ($homeworkResults as &$homeworkResult) {
            $task = $this->getTaskService()->getTaskByActivityId($homeworkResult['lessonId']);
            $homeworkResult['taskId'] = $task['id'];
        }

        return array($homeworks, $homeworkResults);
    }

    private function findRecentStartTask($userId, $courseIds)
    {
        $taskResults = $this->getTaskResultService()->searchTaskResults(
            array('userId' => $userId, 'courseIds' => $courseIds),
            array('createdTime' => 'DESC'),
            0, 1
        );

        $taskResult = array_shift($taskResults);

        if (empty($taskResult)) {
            return array();
        }

        $task = $this->getTaskService()->getTask($taskResult['courseTaskId']);

        return $task;
    }

    private function calculateLearnTime($userId, $courses)
    {
        $totalLearnTime = 0;
        foreach ($courses as $key => &$course) {
            $course['finishedTaskNum'] = $this->getTaskResultService()->countTaskResults(array(
                'courseId' => $key,
                'userId' => $userId,
                'status' => 'finish',
            ));

            $course['learnTime'] = $this->getTaskResultService()->sumLearnTimeByCourseIdAndUserId($key, $userId);
            $totalLearnTime += $course['learnTime'];
        }

        return array($courses, $totalLearnTime);
    }

    protected function getPostCourseService()
    {
        return $this->createService('CorporateTrainingBundle:PostCourse:PostCourseService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    protected function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }

    protected function getHomeworkService()
    {
        return $this->createService('Homework:Homework.HomeworkService');
    }

    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }

    protected function getActivityLearnLogService()
    {
        return $this->createService('Activity:ActivityLearnLogService');
    }

    protected function getOfflineActivityMemberService()
    {
        return $this->createService('OfflineActivity:MemberService');
    }

    protected function getOfflineActivityService()
    {
        return $this->createService('OfflineActivity:OfflineActivityService');
    }

    protected function getCategoryService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineActivity:CategoryService');
    }
}
