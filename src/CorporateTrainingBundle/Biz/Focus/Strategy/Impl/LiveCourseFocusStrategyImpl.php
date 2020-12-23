<?php

namespace CorporateTrainingBundle\Biz\Focus\Strategy\Impl;

use AppBundle\Common\ArrayToolkit;
use CorporateTrainingBundle\Biz\Course\Service\CourseService;
use CorporateTrainingBundle\Biz\Course\Service\CourseSetService;
use CorporateTrainingBundle\Biz\Course\Service\MemberService;
use CorporateTrainingBundle\Biz\Focus\Strategy\BaseFocusStrategy;
use CorporateTrainingBundle\Biz\Focus\Strategy\FocusStrategy;
use CorporateTrainingBundle\Biz\Task\Service\TaskService;

class LiveCourseFocusStrategyImpl extends BaseFocusStrategy implements FocusStrategy
{
    public function findFocusAgo($type = 'my', $time)
    {
        $startTime = mktime(0, 0, 0, date('m', $time), date('d', $time), date('Y', $time));
        $courseIds = $this->findFocusLiveCourseIds($type);

        if (empty($courseIds)) {
            return array();
        }

        $conditions = array(
            'courseIds' => $courseIds,
            'status' => 'published',
            'type' => 'live',
            'endTime_LT' => $time,
            'endTime_GE' => $startTime,
        );

        $tasks = $this->getTaskService()->searchTasks(
            $conditions,
            array('endTime' => 'DESC'),
            0,
            PHP_INT_MAX
        );

        $courseSets = $this->getCourseSetService()->findCourseSetsByIds($courseIds);
        foreach ($tasks as &$task) {
            $task['courseSet'] = $courseSets[$task['fromCourseSetId']];
        }

        return $tasks;
    }

    public function findFocusNow($type = 'my', $time)
    {
        $courseIds = $this->findFocusLiveCourseIds($type);

        if (empty($courseIds)) {
            return array();
        }

        $conditions = array(
            'courseIds' => $courseIds,
            'status' => 'published',
            'type' => 'live',
            'startTime_LE' => $time,
            'endTime_GT' => $time,
        );

        $tasks = $this->getTaskService()->searchTasks(
            $conditions,
            array('endTime' => 'ASC'),
            0,
            5
        );

        $courseSets = $this->getCourseSetService()->findCourseSetsByIds($courseIds);
        foreach ($tasks as &$task) {
            $task['courseSet'] = $courseSets[$task['fromCourseSetId']];
        }

        return $tasks;
    }

    public function findFocusLater($type = 'my', $time)
    {
        $endTime = mktime(0, 0, 0, date('m', $time), date('d', $time) + 1, date('Y', $time)) - 1;
        $courseIds = $this->findFocusLiveCourseIds($type);

        if (empty($courseIds)) {
            return array();
        }

        $conditions = array(
            'courseIds' => $courseIds,
            'status' => 'published',
            'type' => 'live',
            'startTime_GE' => $time,
            'endTime_LT' => $endTime,
        );

        $tasks = $this->getTaskService()->searchTasks(
            $conditions,
            array('startTime' => 'ASC'),
            0,
            5
        );

        $courseSets = $this->getCourseSetService()->findCourseSetsByIds($courseIds);
        foreach ($tasks as &$task) {
            $task['courseSet'] = $courseSets[$task['fromCourseSetId']];
        }

        return $tasks;
    }

    public function findFocusByStartTimeAndEndTime($type = 'my', $startTime, $endTime)
    {
        $courseIds = $this->findFocusLiveCourseIds($type);

        if (empty($courseIds)) {
            return array();
        }

        $conditions = array(
            'courseIds' => $courseIds,
            'status' => 'published',
            'type' => 'live',
            'endTime_GE' => $startTime,
            'startTime_LT' => $endTime,
        );

        $tasks = $this->getTaskService()->searchTasks(
            $conditions,
            array('id' => 'ASC'),
            0,
            PHP_INT_MAX
        );

        $courseSets = $this->getCourseSetService()->findCourseSetsByCourseIds($courseIds);
        foreach ($tasks as &$task) {
            $task['courseSet'] = $courseSets[$task['fromCourseSetId']];
        }

        return $tasks;
    }

    protected function findFocusLiveCourseIds($type)
    {
        $currentUser = $this->getCurrentUser();
        $courseIds = array();

        if ('my' == $type) {
            $courseIds = array_merge(
                $courseIds,
                $this->findMyCreateLiveCourseIds(),
                $this->findMyTeachingLiveCourseIds()
            );
        }

        if (!$currentUser->hasPermission('admin_course_show') && !($currentUser->hasPermission('admin_train_teach_manage_my_teaching_courses_manage') && $currentUser->hasPermission('admin_train_teach_manage_my_teaching_classrooms_manage') && $currentUser->hasPermission('admin_train_teach_manage_project_plan_teaching_manage'))) {
            $courseIds = array();
        }

        return $courseIds;
    }

    protected function findMyCreateLiveCourseIds()
    {
        $currentUser = $this->getCurrentUser();
        $conditions = array(
            'creator' => $currentUser['id'],
            'type' => 'live',
        );
        $courses = $this->getCourseService()->searchCourses(
            $conditions,
            array('id' => 'ASC'),
            0,
            PHP_INT_MAX
        );

        if (empty($courses)) {
            return array();
        }

        return ArrayToolkit::column($courses, 'id');
    }

    protected function findMyTeachingLiveCourseIds()
    {
        $currentUser = $this->getCurrentUser();
        $members = $this->getMemberService()->findTeacherMembersByUserId($currentUser['id']);

        if (empty($members)) {
            return array();
        }

        return ArrayToolkit::column($members, 'courseId');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('CorporateTrainingBundle:Course:CourseSetService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('CorporateTrainingBundle:Course:CourseService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('CorporateTrainingBundle:Task:TaskService');
    }

    /**
     * @return MemberService
     */
    protected function getMemberService()
    {
        return $this->createService('CorporateTrainingBundle:Course:MemberService');
    }
}
