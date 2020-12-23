<?php

namespace AppBundle\Component\Export\Course;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Component\Export\Exporter;

class OverviewStudentExporter extends Exporter
{
    public function canExport()
    {
        $user = $this->getUser();
        try {
            $hasPermission = $user->hasPermission('admin_course_manage');
        } catch (\Exception $e) {
            return false;
        }

        return $user->isAdmin() || $hasPermission;
    }

    public function getCount()
    {
        return $this->getCourseMemberService()->countMembers($this->conditions);
    }

    public function getTitles()
    {
        $titles = array(
            'task.learn_data_detail.nickname',
            'task.learn_data_detail.truename',
            'task.learn_data_detail.finished_rate',
        );
        $tasks = $this->getAllTaskByCourseId();

        $taskTitles = ArrayToolkit::column($tasks, 'title');

        return array_merge($titles, $taskTitles);
    }

    public function getContent($start, $limit)
    {
        $course = $this->getCourseService()->getCourse($this->parameter['courseId']);

        $members = $this->getCourseMemberService()->searchMembers(
            $this->conditions,
            $this->parameter['orderBy'],
            $start,
            $limit
        );

        $userIds = ArrayToolkit::column($members, 'userId');
        $taskCount = $this->countTasksByCourseId();

        list($users, $userProfiles, $tasks, $taskResults) = $this->getReportService()->getStudentDetail($course['id'], $userIds, $taskCount);

        $datas = array();

        $status = array(
            'finish' => $this->trans('course.member_learn_status.learned'),
            'start' => $this->trans('course.member_learn_status.learning'),
        );

        foreach ($members as $member) {
            $userTaskResults = !empty($taskResults[$member['userId']]) ? $taskResults[$member['userId']] : array();

            $user = $users[$member['userId']];
            $userProfile = $userProfiles[$member['userId']];
            $data = array();
            $data[] = $user['nickname'];
            $data[] = $userProfile['truename'];

            $learnProccess = (empty($member['learnedCompulsoryTaskNum']) || empty($course['compulsoryTaskNum'])) ? 0 : (int) ($member['learnedCompulsoryTaskNum'] * 100 / $course['compulsoryTaskNum']);
            $data[] = $learnProccess > 100 ? '100%' : $learnProccess.'%';

            foreach ($tasks as $task) {
                $taskResult = !empty($userTaskResults[$task['id']]) ? $userTaskResults[$task['id']] : array();
                $data[] = empty($taskResult) ? $this->trans('course.member_learn_status.not_start') : $status[$taskResult['status']];
            }

            $datas[] = $data;
        }

        return $datas;
    }

    private function getAllTaskByCourseId()
    {
        return $this->getTaskService()->searchTasks(
            array(
                'courseId' => $this->parameter['courseId'],
                'isOptional' => 0,
                'status' => 'published',
            ),
            array('seq' => 'ASC'),
            0,
            PHP_INT_MAX
        );
    }

    private function countTasksByCourseId()
    {
        return $this->getTaskService()->countTasks(
            array(
                'courseId' => $this->parameter['courseId'],
                'isOptional' => 0,
                'status' => 'published',
            )
        );
    }

    public function buildParameter($conditions)
    {
        $parameter = parent::buildParameter($conditions);
        $parameter['courseId'] = $conditions['courseId'];
        $parameter['orderBy'] = $this->getReportService()->buildStudentDetailOrderBy($conditions);

        return $parameter;
    }

    public function buildCondition($conditions)
    {
        return $this->getReportService()->buildStudentDetailConditions($conditions, $conditions['courseId']);
    }

    protected function getReportService()
    {
        return $this->getBiz()->service('Course:ReportService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    protected function getCourseMemberService()
    {
        return $this->getBiz()->service('Course:MemberService');
    }
}
