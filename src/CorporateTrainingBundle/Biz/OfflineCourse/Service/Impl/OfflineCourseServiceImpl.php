<?php

namespace CorporateTrainingBundle\Biz\OfflineCourse\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Codeages\Biz\Framework\Event\Event;
use CorporateTrainingBundle\Biz\OfflineCourse\Service\OfflineCourseService;

class OfflineCourseServiceImpl extends BaseService implements OfflineCourseService
{
    public function createOfflineCourse($offlineCourse)
    {
        $this->validateFields($offlineCourse);
        $offlineCourse = $this->filterFields($offlineCourse);
        $offlineCourse['creator'] = $this->getCurrentUser()->getId();

        return $this->getOfflineCourseDao()->create($offlineCourse);
    }

    public function updateOfflineCourse($id, $fields)
    {
        $fields = $this->filterFields($fields);

        return $this->getOfflineCourseDao()->update($id, $fields);
    }

    public function deleteOfflineCourse($id)
    {
        return $this->getOfflineCourseDao()->delete($id);
    }

    public function getOfflineCourse($id)
    {
        return $this->getOfflineCourseDao()->get($id);
    }

    public function findOfflineCoursesByIds($ids)
    {
        return $this->getOfflineCourseDao()->findByIds($ids);
    }

    public function findTeachingOfflineCourseByUserId($userId)
    {
        return $this->getOfflineCourseDao()->search(array('teacherId' => '|'.$userId.'|'), array('createdTime' => 'ASC'), 0, PHP_INT_MAX);
    }

    public function searchOfflineCourses($conditions, $orderBys, $start, $limit)
    {
        if (isset($conditions['teacherId'])) {
            $conditions['teacherId'] = '|'.$conditions['teacherId'].'|';
        }

        return $this->getOfflineCourseDao()->search($conditions, $orderBys, $start, $limit);
    }

    public function countOfflineCourses($conditions)
    {
        return $this->getOfflineCourseDao()->count($conditions);
    }

    public function publishOfflineCourse($id)
    {
        return $this->updateOfflineCourse($id, array('status' => 'published'));
    }

    public function closeOfflineCourse($id)
    {
        return $this->updateOfflineCourse($id, array('status' => 'closed'));
    }

    public function findPublishedOfflineCoursesByTeacherIdAndTimeRange($teacherId, $timeRange)
    {
        return $this->getOfflineCourseDao()->findPublishedOfflineCoursesByTeacherIdAndTimeRange($teacherId, $timeRange);
    }

    public function setTeachers($id, $teacherIds)
    {
        $oldOfflineCourse = $this->getOfflineCourse($id);
        $offlineCourse = $this->updateOfflineCourse($id, array('teacherIds' => $teacherIds));
        $this->dispatchEvent('offline_course.teacher.set', new Event($offlineCourse, array('oldOfflineCourse' => $oldOfflineCourse)));

        return $offlineCourse;
    }

    public function findOfflineCourseItems($id)
    {
        $offlineCourse = $this->getOfflineCourse($id);
        if (empty($offlineCourse)) {
            throw $this->createNotFoundException("OfflineCourse#{$id} Not Found");
        }

        $tasks = $this->findTasksByOfflineCourseId($id);

        return $this->prepareCourseItems($tasks);
    }

    public function prepareCourseItems($tasks)
    {
        $items = array();
        foreach ($tasks as $task) {
            $task['itemType'] = 'task';
            $items["task-{$task['id']}"] = $task;
        }

        uasort(
            $items,
            function ($item1, $item2) {
                return $item1['seq'] > $item2['seq'];
            }
        );

        return $items;
    }

    protected function findTasksByOfflineCourseId($id)
    {
        return $this->getOfflineCourseTaskService()->findTasksFetchActivityByOfflineCourseId($id);
    }

    public function tryManageOfflineCourse($id)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException('user not login');
        }

        $offlineCourse = $this->getOfflineCourseDao()->get($id);

        if (empty($offlineCourse)) {
            throw $this->createNotFoundException("OfflineCourse#{$id} Not Found");
        }

        if (!$this->hasOfflineCourseManageRole($id)) {
            throw $this->createAccessDeniedException('can not access');
        }

        return $offlineCourse;
    }

    public function hasOfflineCourseManageRole($id = 0)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            return false;
        }

        if ($user->hasPermission('admin_project_plan_manage')) {
            return true;
        }

        if (empty($id)) {
            return $user->isTeacher();
        }

        $offlineCourse = $this->getOfflineCourseDao()->get($id);

        if (empty($offlineCourse)) {
            return false;
        }

        if ($offlineCourse['creator'] == $user->getId()) {
            return true;
        }

        if (in_array($user->getId(), $offlineCourse['teacherIds'])) {
            return true;
        }

        return false;
    }

    public function findTeachersByOfflineCourseId($id)
    {
        $offlineCourse = $this->getOfflineCourse($id);
        $teachers = $this->getUserService()->findUsersByIds($offlineCourse['teacherIds']);
        $teachersMap = array();
        if (!empty($teachers)) {
            foreach ($teachers as $teacher) {
                $teachersMap[] = array(
                    'id' => $teacher['id'],
                    'nickname' => $teacher['nickname'],
                    'smallAvatar' => $teacher['smallAvatar'],
                );
            }
        }

        return $teachersMap;
    }

    public function findPublishedCourseByUserIds($userIds)
    {
        $offlineCourses = $this->getOfflineCourseDao()->findPublishedCourseByUserIds($userIds);

        return ArrayToolkit::index($offlineCourses, 'id');
    }

    /**
     * @param $course
     *
     * @return mixed
     *               构建线下课程统计数据
     */
    public function buildOfflineCourseStatistic($course)
    {
        $conditions = array('type' => 'offlineCourse', 'offlineCourseId' => $course['id']);

        $offlineCourseTasks = $this->getOfflineCourseTaskService()->searchTasks(
            $conditions,
            array(),
            0,
            PHP_INT_MAX
        );
        $taskIds = ArrayToolkit::column($offlineCourseTasks, 'id');
        $taskIds = empty($taskIds) ? array(-1) : $taskIds;
        $members = $this->getProjectPlanMemberService()->searchProjectPlanMembers(
            array('projectPlanId' => $course['projectPlanId']),
            array(),
            0,
            PHP_INT_MAX
        );
        $course['memberCount'] = count($members);
        $userIds = empty($members) ? array(-1) : ArrayToolkit::column($members, 'userId');

        $hasHomeTasks = $this->getOfflineCourseTaskService()->searchTasks(array_merge($conditions, array('hasHomework' => 1)), array(), 0, PHP_INT_MAX);
        $course['hasHomeTaskCount'] = count($hasHomeTasks) * $course['memberCount'];

        $taskResultConditions = array('userIds' => $userIds, 'offlineCourseId' => $course['id'], 'taskIds' => $taskIds);
        $passHomeworkResult = $this->getOfflineCourseTaskService()->searchTaskResults(array_merge($taskResultConditions, array('homeworkStatus' => 'passed')), array(), 0, PHP_INT_MAX);

        $course['passHomeworkCount'] = count($passHomeworkResult);
        $course['attendTaskCount'] = count($offlineCourseTasks) * $course['memberCount'];
        $attendResult = $this->getOfflineCourseTaskService()->searchTaskResults(array_merge($taskResultConditions, array('attendStatus' => 'attended')), array(), 0, PHP_INT_MAX);
        $course['attendCount'] = count($attendResult);

        return $course;
    }

    protected function validateFields($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('title'), true)) {
            throw $this->createInvalidArgumentException('Lack of required fields');
        }
    }

    protected function filterFields($fields)
    {
        return ArrayToolkit::parts(
            $fields,
            array(
                'title',
                'summary',
                'cover',
                'categoryId',
                'projectPlanId',
                'taskNum',
                'status',
                'startTime',
                'endTime',
                'teacherIds',
                'creator',
                'projectPlanId',
                )
        );
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\Impl\MemberServiceImpl
     */
    protected function getProjectPlanMemberService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:MemberService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\User\Service\Impl\UserServiceImpl
     */
    protected function getUserService()
    {
        return $this->createService('CorporateTrainingBundle:User:UserService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineCourse\Service\Impl\TaskServiceImpl
     */
    protected function getOfflineCourseTaskService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineCourse:TaskService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineCourse\Dao\Impl\OfflineCourseDaoImpl
     */
    public function getOfflineCourseDao()
    {
        return $this->createDao('CorporateTrainingBundle:OfflineCourse:OfflineCourseDao');
    }
}
