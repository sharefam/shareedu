<?php

namespace CorporateTrainingBundle\Controller\StudyCenter;

use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use Biz\Task\Service\TaskService;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\BaseController;

class CourseStatisticsController extends BaseController
{
    public function statisticsByCategoriesAction(Request $request, $userId, $source = null)
    {
        $courses = $this->getCourseService()->findUserLearnCourses($userId);

        $coursesGroupByCategory = ArrayToolkit::group($courses, 'categoryId');

        $topFiveCategorySummaryDatas = $this->getTopFiveCategorySummaryDatas($userId, $coursesGroupByCategory);

        $view = 'study-center/course-statistics/statistics-by-categories.html.twig';
        if ('user_learn' == $source) {
            $view = 'data-report/course-statistics/statistics-by-categories.html.twig';
        } elseif ('offline_activity' == $source) {
            $view = 'offline-activity-manage/study-record/statistics-by-categories.html.twig';
        }

        return $this->render($view, array(
            'topFiveCategorySummaryDatas' => $topFiveCategorySummaryDatas,
            'userId' => $userId,
            'tab_types' => $request->get('tab_types', 'learningStatistics'),
            )
        );
    }

    public function detailAction(Request $request, $userId = null)
    {
        if (!$this->hasManageRole($userId)) {
            throw $this->createAccessDeniedException('Access Denied');
        }

        if (!empty($userId)) {
            $user = $this->getUserService()->getUser($userId);
        } else {
            $user = $this->getCurrentUser();
        }
        $conditions = $request->query->all();
        $conditions = $this->prepareCondition($conditions, $user['id']);
        $paginator = new Paginator(
            $request,
            $this->getCourseService()->searchCourseCount($conditions),
        10
        );

        $courses = $this->getCourseService()->searchCourses(
            $conditions,
            array(),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $courses = $this->calculateWatchTimeAndLearnTimeAndMember($user['id'], $courses);

        return $this->render(
            'study-center/course-statistics/course-score-detail.html.twig',
            array(
            'courses' => empty($courses) ? array() : $courses,
            'paginator' => $paginator,
            'user' => $user,
            'userId' => $user['id'],
            )
        );
    }

    public function detailShowAction(Request $request, $courseId, $userId = null)
    {
        if (!empty($userId)) {
            $user = $this->getUserService()->getUser($userId);
        } else {
            $user = $this->getCurrentUser();
        }

        list($testPapers, $testPaperResults) = $this->findTestPapersAndResults($courseId, $user['id']);

        $activityIds = ArrayToolkit::column($testPaperResults, 'lessonId');
        $tasks = $this->getTaskService()->findTasksByActivityIds($activityIds);

        return $this->render(
            'study-center/course-statistics/course-score-detail-modal.html.twig',
            array(
                'testPaperResults' => empty($testPaperResults) ? array() : $testPaperResults,
                'testPapers' => ArrayToolkit::index($testPapers, 'id'),
                'user' => $user,
                'tasks' => $tasks,
            )
        );
    }

    protected function prepareCondition($conditions, $userId)
    {
        if (empty($conditions['learnStatus']) || 'all' == $conditions['learnStatus']) {
            $courses = $this->getCourseService()->findUserLearnCourses($userId);
        } elseif ('learned' == $conditions['learnStatus']) {
            $courses = $this->getCourseService()->findUserLearnedCourses($userId, 0, PHP_INT_MAX);
        } elseif ('learning' == $conditions['learnStatus']) {
            $courses = $this->getCourseService()->findUserLearningCourses($userId, 0, PHP_INT_MAX);
        }

        $conditions['courseSetIds'] = empty($courses) ? array(-1) : ArrayToolkit::column($courses, 'courseSetId');
        unset($conditions['learnStatus']);

        return $conditions;
    }

    private function hasManageRole($userId)
    {
        $user = $this->getUser($userId);
        if (empty($user)) {
            throw new NotFoundException('User'.$userId.'Not Found');
        }
        $user = $this->getCurrentUser();

        if ($this->getCurrentUser()->getId() == $userId || $this->getCurrentUser()->hasPermission('admin_user_learn_data')) {
            return true;
        }

        $canManage = false;
        $roles = array(
            'ROLE_TRAINING_ADMIN',
            'ROLE_DEPARTMENT_ADMIN',
            'ROLE_SUPER_ADMIN',
        );
        foreach ($roles as $role) {
            if (in_array($role, $user['roles'])) {
                $canManage = true;
                break;
            }
        }

        return $canManage;
    }

    private function getTopFiveCategorySummaryDatas($userId, $coursesGroupByCategory)
    {
        $coursesGroupByCategoryCount = array();
        foreach ($coursesGroupByCategory as $key => $courses) {
            $coursesGroupByCategoryCount[$key] = count($courses);
        }
        arsort($coursesGroupByCategoryCount);

        $count = 1;
        $topFiveCategoryDatas = array();
        foreach ($coursesGroupByCategoryCount as $key => $value) {
            if ($count++ > 5) {
                break;
            }
            $topFiveCategoryDatas[$key]['courseNum'] = $value;
            $categoryIds = ArrayToolkit::column($coursesGroupByCategory[$key], 'categoryId');
            $courseIds = ArrayToolkit::column($coursesGroupByCategory[$key], 'id');
            $conditions['courseIds'] = !empty($courseIds) ? $courseIds : array(-1);
            $conditions['userId'] = $userId;
            $coursesWatchTime = $this->getTaskResultService()->sumWatchTimeByCategoryIdAndUserId($categoryIds[0], $userId);
            $topFiveCategoryDatas[$key]['watchTime'] = $coursesWatchTime;
            $coursesLearnTime = $this->getTaskResultService()->sumLearnTimeByCategoryIdAndUserId($categoryIds[0], $userId);
            $topFiveCategoryDatas[$key]['learnTime'] = $coursesLearnTime;
        }

        return $topFiveCategoryDatas;
    }

    private function calculateWatchTimeAndLearnTimeAndMember($userId, &$courses)
    {
        foreach ($courses as &$course) {
            $course['watchTime'] = $this->getTaskResultService()->sumWatchTimeByCourseIdAndUserId($course['id'], $userId);

            $course['learnTime'] = $this->getTaskResultService()->sumLearnTimeByCourseIdAndUserId($course['id'], $userId);

            $finishedTime = $this->getMemberService()->getUserCourseFinishedTimeByCourseIdAndUserId($course['id'], $userId);

            if (!empty($finishedTime)) {
                $course['finishedTime'] = $finishedTime;
            }
        }

        return $courses;
    }

    private function findTestPapersAndResults($courseId, $userId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        $testPapers = $this->getTestpaperService()->searchTestpapers(
            array('courseSetId' => $course['courseSetId'], 'status' => 'open', 'type' => 'testpaper'),
            array('createdTime' => 'DESC'),
            0, PHP_INT_MAX
        );

        $testPaperResults = array();
        if (!empty($testPapers)) {
            $testPaperResults = $this->getTestpaperService()->searchTestpaperResults(
                array('testIds' => ArrayToolkit::column($testPapers, 'id'), 'userId' => $userId),
                array('id' => 'DESC'),
                0, PHP_INT_MAX
            );
        }

        return array($testPapers, $testPaperResults);
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    protected function getMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    protected function getActivityLearnLogService()
    {
        return $this->createService('Activity:ActivityLearnLogService');
    }

    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }

    protected function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }
}
