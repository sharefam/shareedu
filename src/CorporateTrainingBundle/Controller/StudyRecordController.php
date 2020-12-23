<?php

namespace CorporateTrainingBundle\Controller;

use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\MemberService;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;
use CorporateTrainingBundle\Biz\Course\Service\CourseService;
use CorporateTrainingBundle\Biz\PostCourse\Service\PostCourseService;
use CorporateTrainingBundle\Biz\Task\Service\TaskResultService;
use CorporateTrainingBundle\Biz\Task\Service\TaskService;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\BaseController;
use AppBundle\Common\Paginator;
use CorporateTrainingBundle\Common\DateToolkit;

class StudyRecordController extends BaseController
{
    public function projectPlanRecordAction(Request $request, $userId)
    {
        if (!$this->hasManageRole($userId)) {
            return $this->createMessageResponse('warning', '您没有权限查看');
        }
        $conditions = array('userId' => $userId);
        $memberCount = $this->getProjectPlanMemberService()->countProjectPlanMembers($conditions);

        $paginator = new Paginator(
            $request,
            $memberCount,
            10
        );

        $members = $this->getProjectPlanMemberService()->searchProjectPlanMembers(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        if (!empty($members)) {
            $projectPlanIds = ArrayToolkit::column($members, 'projectPlanId');
            $members = ArrayToolkit::index($members, 'projectPlanId');

            $projectPlans = $this->getProjectPlanService()->searchProjectPlans(
                array('ids' => $projectPlanIds),
                array('startTime' => 'DESC'),
                0,
                PHP_INT_MAX
            );
            foreach ($projectPlans as &$projectPlan) {
                $projectPlan['progress'] = $this->getProjectPlanService()->getPersonalProjectPlanProgress($projectPlan['id'], $userId);
                $projectPlan['member'] = $members[$projectPlan['id']];
                $projectPlan['month'] = date('m', $projectPlan['startTime']);
                $projectPlan['day'] = date('d', $projectPlan['startTime']);
                $projectPlan['year'] = date('Y', $projectPlan['startTime']);
            }
            $projectPlans = ArrayToolkit::group($projectPlans, 'year');
        }

        return $this->render('study-record/project-plan-record.html.twig',
            array(
                'projectPlans' => !empty($projectPlans) ? $projectPlans : array(),
                'userId' => $userId,
                'paginator' => $paginator,
            )
        );
    }

    public function courseListAction($id, $userId)
    {
        $projectPlan = $this->getProjectPlanService()->getProjectPlan($id);

        $projectPlanItems = $this->getProjectPlanService()->searchProjectPlanItems(
            array('projectPlanId' => $id),
            array('seq' => 'ASC'),
            0,
            PHP_INT_MAX
        );

        $projectPlanItems = $this->buildProjectPlanItems($projectPlanItems, $userId);

        return $this->render('study-record/project-plan-course-list.html.twig', array(
            'projectPlanItems' => $projectPlanItems,
            'projectPlan' => $projectPlan,
            'userId' => $userId,
        ));
    }

    public function offlineCourseTaskListAction($projectPlanItem, $userId)
    {
        $offlineCourse = $this->getOfflineCourseService()->getOfflineCourse($projectPlanItem['targetId']);
        $offlineCourseItems = $this->getOfflineCourseService()->findOfflineCourseItems($projectPlanItem['targetId']);

        $member = $this->getOfflineCourseMemberService()->findMembersByOfflineCourseId($offlineCourse['id']);

        return $this->render(
            'study-record/offline-course/list.html.twig',
            array(
                'offlineCourse' => $offlineCourse,
                'offlineCourseItems' => $offlineCourseItems,
                'member' => $member,
                'userId' => $userId,
            )
        );
    }

    public function offlineCourseStudyRecordAction($offlineCourseId, $userId)
    {
        $hasHomeWorkTasks = $this->getOfflineCourseTaskService()->searchTasks(array('offlineCourseId' => $offlineCourseId, 'hasHomework' => 1), array(), 0, PHP_INT_MAX);

        return $this->render(
            'study-record/offline-course/score.html.twig',
            array(
                'homeworks' => $hasHomeWorkTasks,
                'userId' => $userId,
            )
        );
    }

    public function offlineExamAction($projectPlanItem, $userId)
    {
        $offlineExam = $this->getOfflineExamService()->getOfflineExam($projectPlanItem['targetId']);

        return $this->render('study-record/offline-exam/detail.html.twig',
            array(
                'userId' => $userId,
                'offlineExam' => $offlineExam,
            ));
    }

    public function examAction($projectPlanItem, $userId)
    {
        $exam = $this->getExamService()->getExam($projectPlanItem['targetId']);
        $testpaper = $this->getTestpaperService()->getTestpaper($exam['testPaperId']);
        $examResult = $this->getExamService()->getUserBestExamResult($userId, $exam['id']);

        return $this->render('study-record/exam/detail.html.twig', array(
            'userId' => $userId,
            'exam' => $exam,
            'testpaper' => $testpaper,
            'examResult' => $examResult,
        ));
    }

    public function offlineActivityRecordAction(Request $request, $userId)
    {
        if (!$this->hasManageRole($userId)) {
            return $this->createMessageResponse('warning', '您没有权限查看');
        }
        $members = $this->getOfflineActivityMemberService()->searchMembers(
            array('userId' => $userId),
            array(),
            0,
            PHP_INT_MAX
        );

        $paginator = new Paginator(
            $request,
            count($members),
            10
        );

        if (!empty($members)) {
            $offlineActivityIds = ArrayToolkit::column($members, 'offlineActivityId');
            $members = ArrayToolkit::index($members, 'offlineActivityId');

            $offlineActivities = $this->getOfflineActivityService()->searchOfflineActivities(
                array('ids' => $offlineActivityIds),
                array('startTime' => 'DESC'),
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
            );

            foreach ($offlineActivities as &$offlineActivity) {
                $offlineActivity['member'] = $members[$offlineActivity['id']];
                $offlineActivity['month'] = date('m', $offlineActivity['startTime']);
                $offlineActivity['day'] = date('d', $offlineActivity['startTime']);
                $offlineActivity['year'] = date('Y', $offlineActivity['startTime']);
            }
            $offlineActivities = ArrayToolkit::group($offlineActivities, 'year');
        } else {
            $offlineActivities = array();
        }

        return $this->render(
            'study-record/offline-activity/offline-activity.html.twig',
            array(
                'offlineActivities' => $offlineActivities,
                'paginator' => $paginator,
                'type' => 'offline_activity',
                'userId' => $userId,
            )
        );
    }

    public function offlineActivityEnrollmentRecordAction(Request $request, $userId)
    {
        if (!$this->hasManageRole($userId)) {
            return $this->createMessageResponse('warning', '您没有权限查看');
        }
        $conditions['userId'] = $userId;

        $count = $this->getOfflineActivityEnrollmentRecordService()->countEnrollmentRecords($conditions);
        $paginator = new Paginator(
            $request,
            $count,
            10
        );

        $enrollmentRecords = $this->getOfflineActivityEnrollmentRecordService()->searchEnrollmentRecords(
            $conditions,
            array('submittedTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $offlineActivityIds = ArrayToolkit::column($enrollmentRecords, 'offlineActivityId');
        $offlineActivities = $this->getOfflineActivityService()->findOfflineActivitiesByIds($offlineActivityIds);
        $offlineActivities = ArrayToolkit::index($offlineActivities, 'id');

        foreach ($enrollmentRecords as &$enrollmentRecord) {
            $enrollmentRecord['offlineActivity'] = $offlineActivities[$enrollmentRecord['offlineActivityId']];
        }

        return $this->render(
            'study-record/offline-activity/offline-activity.html.twig',
            array(
                'enrollmentRecords' => $enrollmentRecords,
                'type' => 'enrollment_record',
                'paginator' => $paginator,
                'userId' => $userId,
            )
        );
    }

    public function postCourseRecordAction(Request $request, $userId)
    {
        if (!$this->hasManageRole($userId)) {
            return $this->createMessageResponse('warning', '您没有权限查看');
        }

        $user = $this->getUserService()->getUser($userId);
        $postCourses = $this->getPostCourseService()->findPostCoursesByPostId($user['postId']);

        if (!empty($postCourses)) {
            $courseIds = ArrayToolkit::column($postCourses, 'courseId');
            $courses = $this->getCourseService()->findCoursesByIds($courseIds);
            list($courses, $totalLearnTime) = $this->calculateLearnTime($user['id'], $courses);
            $learnedCoursesNum = $this->getPostCourseService()->countUserLearnedPostCourses($user['id'], $courseIds);
            $recentLearnTask = $this->findRecentLearnTask($user['id'], $courseIds);
        }

        return $this->render(
            'study-record/post-course-record.html.twig',
            array(
                'postCourseCount' => empty($postCourses) ? 0 : count($postCourses),
                'totalLearnTime' => empty($totalLearnTime) ? 0 : $totalLearnTime,
                'learnedCoursesNum' => empty($learnedCoursesNum) ? 0 : $learnedCoursesNum,
                'recentLearnTask' => empty($recentLearnTask) ? array() : $recentLearnTask,
                'user' => $user,
                'userId' => $userId,
            )
        );
    }

    public function postCourseListAction(Request $request, $userId)
    {
        if (!$this->hasManageRole($userId)) {
            return $this->createMessageResponse('warning', '您没有权限查看');
        }

        $user = $this->getUserService()->getUser($userId);
        $postCourses = $this->getPostCourseService()->findPostCoursesByPostId($user['postId']);

        if (!empty($postCourses)) {
            $courseIds = ArrayToolkit::column($postCourses, 'courseId');
            $courses = $this->getCourseService()->findCoursesByIds($courseIds);
            foreach ($courses as $key => &$course) {
                $course['learnTime'] = $this->getTaskResultService()->sumLearnTimeByCourseIdAndUserId($key, $userId);
                $course['isCourseMember'] = $this->getMemberService()->isCourseMember($course['id'], $userId);
            }
        }

        return $this->render(
            'study-record/post-course-list.html.twig',
            array(
                'courses' => empty($courses) ? array() : $courses,
                'tab_type' => 'record',
                'userId' => $user['id'],
            )
        );
    }

    public function statisticsOverviewAction(Request $request, $userId)
    {
        if (!$this->hasManageRole($userId)) {
            return $this->createMessageResponse('warning', '您没有权限查看');
        }

        $learnCourses = $this->getCourseService()->findUserLearnCourses($userId);

        $learnCourseIds = ArrayToolkit::column($learnCourses, 'id');
        $learnCourseCount = count($learnCourses);
        $coursesLearnTime = $this->getTaskResultService()->sumLearnTimeByCourseIdsAndUserIdsGroupByUserId($learnCourseIds, array($userId));

        list($startDateTime, $endDateTime) = DateToolkit::generateStartDateAndEndDate('year', 0, 'date');

        $yearTotalDataConditions = array('startDateTime' => strtotime($startDateTime), 'endDateTime' => strtotime($endDateTime), 'userId' => $userId);
        $yearFinishedCourseNum = $this->getUserDailyLearnRecordService()->countFinishedCourseNumByConditions($yearTotalDataConditions);
        $yearLearnTime = $this->getUserDailyLearnRecordService()->sumLearnTimeByConditions($yearTotalDataConditions);

        $totalDataConditions = array('userId' => $userId);
        $totalFinishedCourseNum = $this->getUserDailyLearnRecordService()->countFinishedCourseNumByConditions($totalDataConditions);
        $totalLearnTime = $this->getUserDailyLearnRecordService()->sumLearnTimeByConditions($totalDataConditions);

        return $this->render(
            'study-record/online-course-record/statistics-overview.html.twig',
            array(
                'learnCourseCount' => $learnCourseCount,
                'coursesLearnTime' => empty($coursesLearnTime) ? '' : $coursesLearnTime[0]['totalLearnTime'],
                'totalData' => array('finishedCourseNum' => $totalFinishedCourseNum, 'totalLearnTime' => $totalLearnTime),
                'yearTotalData' => array('finishedCourseNum' => $yearFinishedCourseNum, 'totalLearnTime' => $yearLearnTime),
                'userId' => $userId,
            )
        );
    }

    private function hasManageRole($userId)
    {
        $user = $this->getUserService()->getUser($userId);
        if (empty($user)) {
            throw new NotFoundException('User'.$userId.'Not Found');
        }
        $currentUser = $this->getCurrentUser();

        if ($this->getCurrentUser()->getId() == $userId) {
            return true;
        }

        $userManageIds = $this->getCurrentUser()->getManageOrgIdsRecursively();
        $orgIds = array_intersect($user['orgIds'], $userManageIds);

        if (empty($orgIds)) {
            return false;
        }

        if ($this->getCurrentUser()->hasPermission('admin_user_learn_data')) {
            return true;
        }

        $canManage = false;
        $roles = array(
            'ROLE_TRAINING_ADMIN',
            'ROLE_DEPARTMENT_ADMIN',
            'ROLE_SUPER_ADMIN',
        );

        foreach ($roles as $role) {
            if (in_array($role, $currentUser['roles'])) {
                $canManage = true;
                break;
            }
        }

        return $canManage;
    }

    private function findRecentLearnTask($userId, $courseIds)
    {
        $taskResults = $this->getTaskResultService()->searchTaskResults(
            array('userId' => $userId, 'courseIds' => $courseIds),
            array('updatedTime' => 'DESC'),
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

    private function buildProjectPlanItems($projectPlanItems, $userId)
    {
        foreach ($projectPlanItems as &$projectPlanItem) {
            $projectPlanItem['allAttendTaskNum'] = 0;
            $projectPlanItem['attendNum'] = 0;
            if ('offline_course' == $projectPlanItem['targetType']) {
                $offlineCourse = $this->getOfflineCourseService()->getOfflineCourse($projectPlanItem['targetId']);
                $offlineCourseTasks = $this->getOfflineCourseTaskService()->searchTasks(array('offlineCourseId' => $offlineCourse['id'], 'type' => 'offlineCourse'), array(), 0, PHP_INT_MAX);
                $offlineCourseTaskAttendNum = count($offlineCourseTasks);
                $taskIds = !empty($offlineCourseTasks) ? ArrayToolkit::column($offlineCourseTasks, 'id') : array(-1);
                $projectPlanItem['allAttendTaskNum'] += $offlineCourseTaskAttendNum;
                $projectPlanItem['attendNum'] += $this->getOfflineCourseTaskService()->countTaskResults(array('offlineCourseId' => $offlineCourse['id'], 'attendStatus' => 'attended', 'userId' => $userId, 'taskIds' => $taskIds));
            }
            if ('course' == $projectPlanItem['targetType']) {
                $projectPlanItem['learnTime'] = $this->getTaskResultService()->sumLearnTimeByCourseIdAndUserId($projectPlanItem['targetId'], $userId);
            }
        }

        return $projectPlanItems;
    }

    /**
     * @return PostCourseService
     */
    protected function getPostCourseService()
    {
        return $this->createService('CorporateTrainingBundle:PostCourse:PostCourseService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return MemberService
     */
    protected function getMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }

    protected function getOfflineActivityMemberService()
    {
        return $this->createService('OfflineActivity:MemberService');
    }

    protected function getCategoryService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineActivity:CategoryService');
    }

    protected function getOfflineActivityService()
    {
        return $this->createService('OfflineActivity:OfflineActivityService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineExam\Service\Impl\OfflineExamServiceImpl
     */
    protected function getOfflineExamService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineExam:OfflineExamService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\Impl\MemberServiceImpl
     */
    protected function getProjectPlanMemberService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:MemberService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineCourse\Service\Impl\MemberServiceImpl
     */
    protected function getOfflineCourseMemberService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineCourse:MemberService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\Impl\ProjectPlanServiceImpl
     */
    protected function getProjectPlanService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:ProjectPlanService');
    }

    protected function getOfflineCourseAttendanceService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:OfflineCourseAttendanceService');
    }

    protected function getOfflineCourseActivityService()
    {
        return $this->createService('CorporateTrainingBundle:Activity:OfflineCourseActivityService');
    }

    protected function getOfflineExamResultService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:OfflineExamResultService');
    }

    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineCourse\Service\Impl\OfflineCourseServiceImpl
     */
    protected function getOfflineCourseService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineCourse:OfflineCourseService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineCourse\Service\Impl\TaskServiceImpl
     */
    protected function getOfflineCourseTaskService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineCourse:TaskService');
    }

    protected function getOfflineCourseHomeworkService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:OfflineCourseHomeworkService');
    }

    protected function getExamService()
    {
        return $this->createService('ExamPlugin:Exam:ExamService');
    }

    protected function getTestpaperService()
    {
        return $this->createService('ExamPlugin:TestPaper:TestPaperService');
    }

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    protected function getOfflineActivityEnrollmentRecordService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineActivity:EnrollmentRecordService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\DataStatistics\Service\Impl\UserDailyLearnRecordServiceImpl
     */
    protected function getUserDailyLearnRecordService()
    {
        return $this->createService('CorporateTrainingBundle:UserDailyLearnRecord:UserDailyLearnRecordService');
    }
}
