<?php

namespace CorporateTrainingBundle\Controller\My;

use AppBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;

class TeachingRecordController extends BaseController
{
    public function indexAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $courseTime = array('startTime' => strtotime(date('Y').'/01/01 00:00'), 'endTime' => time());
        $courseSets = $this->getCourseSetService()->searchUserTeachingCourseSets(
            $user['id'],
            $courseTime,
            0,
            PHP_INT_MAX
        );
        if (!empty($courseSets)) {
            $courseSetIds = ArrayToolkit::column($courseSets, 'id');
            $user['courseStudentNum'] = $this->getCourseSetService()->sumStudentNumByCourseSetIds($courseSetIds);
        }

        $offlineCourses = $this->getOfflineCourseService()->searchOfflineCourses(array_merge($courseTime, array('teacherId' => $user['id'])), array(), 0, PHP_INT_MAX);

        if (!empty($offlineCourses)) {
            $user['offlineCourseStudentNum'] = 0;
            foreach ($offlineCourses as $offlineCourse) {
                $user['offlineCourseStudentNum'] += $this->getProjectPlanMemberService()->countProjectPlanMembers(array('projectPlanId' => $offlineCourse['projectPlanId']));
            }
        }

        if ($this->isPluginInstalled('Survey')) {
            $user['teacherTotalScore'] = $this->getSurveyResultService()->getTeacherOnlineAndOfflineCoursesAverageSurveyScore($user['id']);
        }

        return $this->render(
            'my/teaching-record/index.html.twig',
            array(
                'type' => 'index',
                'user' => $user,
            )
        );
    }

    public function courseAction(Request $request)
    {
        $conditions = $request->query->all();
        $user = $this->getCurrentUser();
        $conditions = $this->buildTeacherCourseArchivesConditions($conditions);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseSetService()->countUserTeachingCourseSets($user['id'], $conditions),
            20
        );

        $courseSets = $this->getCourseSetService()->searchUserTeachingCourseSets(
            $user['id'],
            $conditions,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        foreach ($courseSets as &$courseSet) {
            $courseSet['learnTime'] = $this->getTaskResultService()->sumLearnTimeByCourseId($courseSet['defaultCourseId']);
            $courseSet['learnedStudentNum'] = $this->getCourseMemberService()->countLearnedStudentsByCourseId($courseSet['defaultCourseId']);
            if ($this->isPluginInstalled('Survey')) {
                $courseSet['surveyScore'] = $this->getSurveyResultService()->getOnlineCourseSurveyScoreByCourseId($courseSet['defaultCourseId']);
            }
            $courseSet['averageLearnTime'] = $courseSet['studentNum'] > 0 ? sprintf('%.2f', $courseSet['learnTime'] / $courseSet['studentNum']) : 0;
        }

        $categories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($courseSets, 'categoryId'));
        $allCourseSets = $this->getCourseSetService()->searchUserTeachingCourseSets(
            $user['id'],
            $conditions,
            0,
            PHP_INT_MAX
        );
        $courseIds = ArrayToolkit::column($allCourseSets, 'defaultCourseId');

        $courseTotalScore = 0;
        if ($this->isPluginInstalled('Survey')) {
            $courseTotalScore = $this->getSurveyResultService()->getOnlineCoursesAverageSurveyScore($courseIds);
        }

        return $this->render(
            'my/teaching-record/course.html.twig',
            array(
                'type' => 'course',
                'user' => $user,
                'courseSets' => $courseSets,
                'categories' => $categories,
                'paginator' => $paginator,
                'courseTime' => $conditions['courseTime'],
                'courseTotalScore' => $courseTotalScore,
                'courseCount' => count($allCourseSets),
            )
        );
    }

    public function offlineCourseAction(Request $request)
    {
        $conditions = $request->query->all();

        $user = $this->getCurrentUser();
        $conditions = $this->buildTeacherOfflineCourseArchivesConditions($conditions, $user['id']);
        $paginator = new Paginator(
            $this->get('request'),
            $this->getOfflineCourseService()->countOfflineCourses($conditions),
            20
        );
        $allOfflineCourses = $this->getOfflineCourseService()->searchOfflineCourses(
            $conditions,
            array('createdTime' => 'DESC'),
            0,
            PHP_INT_MAX
        );

        $offlineCourses = $this->getOfflineCourseService()->searchOfflineCourses(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        foreach ($offlineCourses as &$offlineCourse) {
            if ($this->isPluginInstalled('Survey')) {
                $offlineCourse['surveyScore'] = $this->getSurveyResultService()->getOfflineCourseSurveyScoreByCourseIdAndProjectPlanId($offlineCourse['id'], $offlineCourse['projectPlanId']);
            }
            $offlineCourse = $this->getOfflineCourseService()->buildOfflineCourseStatistic($offlineCourse);
        }

        $courseTotalScore = 0;
        if ($this->isPluginInstalled('Survey')) {
            $courseTotalScore = $this->getSurveyResultService()->getOfflineCoursesAverageSurveyScore(ArrayToolkit::column($allOfflineCourses, 'id'));
        }

        return $this->render(
            'my/teaching-record/offline-course.html.twig',
            array(
                'type' => 'offlineCourse',
                'user' => $user,
                'offlineCourses' => $offlineCourses,
                'paginator' => $paginator,
                'courseTime' => $conditions['courseTime'],
                'courseTotalScore' => $courseTotalScore,
                'courseCount' => count($allOfflineCourses),
            )
        );
    }

    public function ajaxGetCourseSurveyScoreDataAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $fields = $request->query->all();
        $fields['date'] = empty($fields['date']) ? 'thisYear' : $fields['date'];
        if ('thisYear' == $fields['date']) {
            $courseTime = array('startTime' => strtotime(date('Y').'/01/01 00:00'), 'endTime' => time());
        } else {
            $courseTime = array('startTime' => strtotime((date('Y') - 1).'/01/01 00:00'), 'endTime' => strtotime(date('Y').'/01/01 00:00'));
        }
        $courseSets = $this->getCourseSetService()->searchUserTeachingCourseSets(
            $user['id'],
            array_merge(array('excludeStatus' => array('draft')), $courseTime),
            0,
            PHP_INT_MAX
        );
        $data = array(0, 0, 0, 0, 0);
        foreach ($courseSets as &$courseSet) {
            $surveyScore = $this->getSurveyResultService()->getOnlineCourseSurveyScoreByCourseId($courseSet['defaultCourseId']);
            $data = $this->buildCourseSurveyScoreData($surveyScore, $data);
        }

        $offlineCourses = $this->getOfflineCourseService()->searchOfflineCourses(array_merge($courseTime, array('teacherId' => $user['id'])), array(), 0, PHP_INT_MAX);
        foreach ($offlineCourses as &$offlineCourse) {
            $surveyScore = $this->getSurveyResultService()->getOfflineCourseSurveyScoreByCourseIdAndProjectPlanId($offlineCourse['id'], $offlineCourse['projectPlanId']);
            $data = $this->buildCourseSurveyScoreData($surveyScore, $data);
        }

        $chartData = array(
            'series' => array(
                'data' => $data,
            ),
        );

        return $this->createJsonResponse($chartData);
    }

    public function courseRankListAction(Request $request, $courseType, $from = null)
    {
        $fields = $request->query->all();
        $from = empty($fields['from']) ? $from : $fields['from'];
        $fields['date'] = empty($fields['date']) ? 'thisYear' : $fields['date'];
        if ('thisYear' == $fields['date']) {
            $courseTime = array('startTime' => strtotime(date('Y').'/01/01 00:00'), 'endTime' => time());
        } else {
            $courseTime = array('startTime' => strtotime((date('Y') - 1).'/01/01 00:00'), 'endTime' => strtotime(date('Y').'/01/01 00:00'));
        }
        $topFiveTeachers = array();

        if ('course' == $courseType) {
            $courseRankData = $this->buildCourseRankOnlineCourseData($courseTime);
        } else {
            $courseRankData = $this->buildCourseRankOfflineCourseData($courseTime);
        }
        if (!empty($courseRankData)) {
            arsort($courseRankData);
            $courseRankData = array_filter($courseRankData);
            $topFiveTeachers = array_slice($courseRankData, 0, 5, true);
        }
        if ('admin' == $from) {
            $view = 'admin/data/teacher/overview-rank-list.html.twig';
        } else {
            $view = 'my/teaching-record/widgets/teacher-course-rank-list.html.twig';
        }

        return $this->render($view, array(
            'topFiveTeachers' => $topFiveTeachers,
            'rankType' => 'course',
            'courseType' => $courseType,
            'date' => $fields['date'],
        ));
    }

    public function studentRankListAction(Request $request, $courseType, $from = null)
    {
        $fields = $request->query->all();
        $from = empty($fields['from']) ? $from : $fields['from'];
        $fields['date'] = empty($fields['date']) ? 'thisYear' : $fields['date'];
        if ('thisYear' == $fields['date']) {
            $courseTime = array('startTime' => strtotime(date('Y').'/01/01 00:00'), 'endTime' => time());
        } else {
            $courseTime = array('startTime' => strtotime((date('Y') - 1).'/01/01 00:00'), 'endTime' => strtotime(date('Y').'/01/01 00:00'));
        }

        if ('course' == $courseType) {
            $studentData = $this->buildStudentRankOnlineCourseData($courseTime);
        } else {
            $studentData = $this->buildStudentRankOfflineCourseData($courseTime);
        }

        $userIds = ArrayToolkit::column($studentData, 'teacherId');
        $studentData = ArrayToolkit::index($studentData, 'teacherId');

        $lockedTeachers = $this->getUserService()->searchUsers(array('roles' => 'ROLE_TEACHER', 'locked' => 1, 'userIds' => $userIds), array(), 0, PHP_INT_MAX);
        if (!empty($lockedTeachers)) {
            $lockedTeacherIds = ArrayToolkit::column($lockedTeachers, 'id');
            foreach ($lockedTeacherIds as $lockedTeacherId) {
                unset($studentData[$lockedTeacherId]);
            }
        }

        if (!empty($studentData)) {
            foreach ($studentData as $key => &$array) {
                if ($array['courseStudentNum'] > 0) {
                    $key_arrays[] = $array['courseStudentNum'];
                } else {
                    unset($studentData[$key]);
                }
            }

            if (!empty($key_arrays)) {
                array_multisort($key_arrays, SORT_DESC, SORT_NUMERIC, $studentData);
                $studentData = array_slice($studentData, 0, 5, true);
            }
        }

        if ('admin' == $from) {
            $view = 'admin/data/teacher/overview-rank-list.html.twig';
        } else {
            $view = 'my/teaching-record/widgets/teacher-course-rank-list.html.twig';
        }

        return $this->render($view, array(
            'topFiveTeachers' => $studentData,
            'rankType' => 'student',
            'courseType' => $courseType,
            'date' => $fields['date'],
        ));
    }

    protected function buildStudentRankOnlineCourseData($courseTime)
    {
        $studentData = array();
        $courseSets = $this->getCourseSetService()->searchCourseSets(
            array_merge(array('excludeStatus' => array('draft')), $courseTime),
            array(),
            0,
            PHP_INT_MAX
        );
        $courseIds = ArrayToolkit::column($courseSets, 'defaultCourseId');
        $teachers = $this->getCourseService()->findTeachersByCourseIds($courseIds);
        if (!empty($teachers)) {
            $teachers = ArrayToolkit::group($teachers, 'userId');
            foreach ($teachers as &$value) {
                $courseSetIds = ArrayToolkit::column($value, 'courseSetId');
                $teacher['courseStudentNum'] = $this->getCourseSetService()->sumStudentNumByCourseSetIds($courseSetIds);
                $teacher['teacherId'] = $value[0]['userId'];
                $studentData[] = $teacher;
            }
        }

        return $studentData;
    }

    protected function buildStudentRankOfflineCourseData($courseTime)
    {
        $studentData = array();

        $teachers = $this->getUserService()->searchUsers(array('roles' => 'ROLE_TEACHER', 'locked' => 0), array(), 0, PHP_INT_MAX);
        $teacherIds = ArrayToolkit::column($teachers, 'id');

        if (!empty($teacherIds)) {
            foreach ($teacherIds as $teacherId) {
                $offlineCourses = $this->getOfflineCourseService()->findPublishedOfflineCoursesByTeacherIdAndTimeRange($teacherId, $courseTime);
                if (!empty($offlineCourses)) {
                    $projectPlanIds = empty($offlineCourses) ? array(-1) : ArrayToolkit::column($offlineCourses, 'projectPlanId');
                    $user['courseStudentNum'] = $this->getProjectPlanMemberService()->countProjectPlanMembers(array('projectPlanIds' => $projectPlanIds));
                    $user['teacherId'] = $teacherId;
                    $studentData[] = $user;
                }
            }
        }

        return $studentData;
    }

    protected function buildCourseRankOnlineCourseData($courseTime)
    {
        $courseSets = $this->getCourseSetService()->searchCourseSets(
            array_merge(array('excludeStatus' => array('draft')), $courseTime),
            array(),
            0,
            PHP_INT_MAX
        );
        $courseIds = ArrayToolkit::column($courseSets, 'defaultCourseId');
        $teachers = $this->getCourseService()->findTeachersByCourseIds($courseIds);
        $courseRankData = ArrayToolkit::column($teachers, 'userId');
        if (!empty($courseRankData)) {
            $userIds = array_unique($courseRankData);
            $courseRankData = array_count_values($courseRankData);
            $lockedTeachers = $this->getUserService()->searchUsers(array('roles' => 'ROLE_TEACHER', 'locked' => 1, 'userIds' => $userIds), array(), 0, PHP_INT_MAX);
            if (!empty($lockedTeachers)) {
                $lockedTeacherIds = ArrayToolkit::column($lockedTeachers, 'id');
                foreach ($lockedTeacherIds as $lockedTeacherId) {
                    unset($courseRankData[$lockedTeacherId]);
                }
            }
        }

        return $courseRankData;
    }

    protected function buildCourseRankOfflineCourseData($courseTime)
    {
        $courseRankData = array();

        $teachers = $this->getUserService()->searchUsers(array('roles' => 'ROLE_TEACHER', 'locked' => 0), array(), 0, PHP_INT_MAX);
        $teacherIds = ArrayToolkit::column($teachers, 'id');

        foreach ($teacherIds as $teacherId) {
            $teachingOfflineCourses = $this->getOfflineCourseService()->findPublishedOfflineCoursesByTeacherIdAndTimeRange($teacherId, $courseTime);
            $courseIds = empty($teachingOfflineCourses) ? array(-1) : ArrayToolkit::column($teachingOfflineCourses, 'id');
            $time = $this->getOfflineCourseTaskService()->statisticsOfflineCourseTimeByTimeRangeAndCourseIds($courseTime, $courseIds);
            $courseRankData[$teacherId] = round(($time / 3600), 1);
        }

        return $courseRankData;
    }

    protected function buildCourseSurveyScoreData($surveyScore, $data)
    {
        if ($surveyScore <= 3) {
            ++$data[0];
        } elseif ($surveyScore > 3 && $surveyScore <= 3.5) {
            ++$data[1];
        } elseif ($surveyScore > 3.5 && $surveyScore <= 4) {
            ++$data[2];
        } elseif ($surveyScore > 4 && $surveyScore <= 4.5) {
            ++$data[3];
        } else {
            ++$data[4];
        }

        return $data;
    }

    protected function buildTeacherOfflineCourseArchivesConditions($conditions, $userId)
    {
        $courseTime = array('startTime' => strtotime(date('Y').'/01/01 00:00'), 'endTime' => time());
        if (!empty($conditions['courseCreateTime'])) {
            $courseCreateTime = explode('-', $conditions['courseCreateTime']);
            $courseTime['startTime'] = strtotime($courseCreateTime[0]);
            $courseTime['endTime'] = strtotime($courseCreateTime[1].' 23:59:59');
        }

        $conditions = array_merge($conditions, $courseTime, array('teacherId' => $userId));
        $conditions['courseTime'] = $courseTime;

        return $conditions;
    }

    protected function buildTeacherCourseArchivesConditions($conditions)
    {
        $courseTime = array('startTime' => strtotime(date('Y').'/01/01 00:00'), 'endTime' => time());

        if (!empty($conditions['courseCreateTime'])) {
            $courseCreateTime = explode('-', $conditions['courseCreateTime']);
            $courseTime['startTime'] = strtotime($courseCreateTime[0]);
            $courseTime['endTime'] = strtotime($courseCreateTime[1].' 23:59:59');
        }

        $conditions = array_merge($conditions, $courseTime);
        $conditions['courseTime'] = $courseTime;

        if (!empty($conditions['categoryId'])) {
            $categoryIds = $this->getCategoryService()->findCategoryChildrenIds($conditions['categoryId']);
            $categoryIds[] = $conditions['categoryId'];
            $conditions['categoryIds'] = $categoryIds;
            unset($conditions['categoryId']);
        }

        return $conditions;
    }

    /**
     * @return \CorporateTrainingBundle\Biz\Course\Service\Impl\CourseServiceImpl
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\Course\Service\Impl\CourseSetServiceImpl
     */
    protected function getCourseSetService()
    {
        return $this->createService('CorporateTrainingBundle:Course:CourseSetService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\Course\Service\Impl\MemberServiceImpl
     */
    protected function getCourseMemberService()
    {
        return $this->createService('CorporateTrainingBundle:Course:MemberService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\Task\Service\Impl\TaskResultServiceImpl
     */
    protected function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineCourse\Service\Impl\OfflineCourseServiceImpl
     */
    protected function getOfflineCourseService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineCourse:OfflineCourseService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineCourse\Service\Impl\MemberServiceImpl
     */
    protected function getOfflineCourseMemberService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineCourse:MemberService');
    }

    /**
     * @return \SurveyPlugin\Biz\Survey\Service\Impl\SurveyResultServiceImpl
     */
    protected function getSurveyResultService()
    {
        return $this->createService('SurveyPlugin:Survey:SurveyResultService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\Impl\MemberServiceImpl
     */
    protected function getProjectPlanMemberService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:MemberService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\Impl\ProjectPlanServiceImpl
     */
    protected function getProjectPlanService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:ProjectPlanService');
    }

    protected function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineCourse\Service\Impl\TaskServiceImpl
     */
    protected function getOfflineCourseTaskService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineCourse:TaskService');
    }
}
