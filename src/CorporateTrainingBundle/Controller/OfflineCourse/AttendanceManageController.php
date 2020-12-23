<?php

namespace CorporateTrainingBundle\Controller\OfflineCourse;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\Paginator;

class AttendanceManageController extends BaseController
{
    public function courseManageAction(Request $request, $id)
    {
        $offlineCourse = $this->getOfflineCourseService()->tryManageOfflineCourse($id);

        $conditions = array('offlineCourseId' => $offlineCourse['id'], 'type' => 'offlineCourse');

        $paginator = new Paginator(
            $request,
            $this->getOfflineCourseTaskService()->countTasks($conditions),
            10
        );

        $tasks = $this->getOfflineCourseTaskService()->searchTasks(
            $conditions,
            array(),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $projectPlanItem = $this->getProjectPlanService()->getProjectPlanItemByTargetIdAndTargetType($offlineCourse['id'], 'offline_course');

        $projectPlanMembers = $this->getProjectPlanMemberService()->searchProjectPlanMembers(
            array('projectPlanId' => $projectPlanItem['projectPlanId']),
            array(),
            0,
            PHP_INT_MAX
        );

        $projectPlanMemberIds = empty($projectPlanMembers) ? array(-1) : ArrayToolkit::column($projectPlanMembers, 'userId');

        foreach ($tasks as &$task) {
            $task['finishedCount'] = $this->getOfflineCourseTaskService()->countTaskResults(array('taskId' => $task['id'], 'attendStatus' => 'attended', 'userIds' => $projectPlanMemberIds));
            $task['unfinishedMemberCount'] = count($projectPlanMembers) - $task['finishedCount'];
        }

        return $this->render('offline-course-manage/attendance-manage/course-manage-attendance.html.twig',
            array(
                'offlineCourse' => $offlineCourse,
                'tasks' => empty($tasks) ? '' : $tasks,
                'paginator' => $paginator,
            )
        );
    }

    public function courseTaskManageAction(Request $request, $id, $taskId)
    {
        $renderData = $this->buildCourseTaskRenderData($request, $id, $taskId);

        return $this->render('offline-course-manage/attendance-manage/course-task-manage-attendance.html.twig', $renderData);
    }

    public function courseTaskListManageAction(Request $request, $id, $taskId)
    {
        $renderData = $this->buildCourseTaskRenderData($request, $id, $taskId, 'ajax');

        return $this->render('offline-course-manage/attendance-manage/course-task-manage-attendance-list.html.twig', $renderData);
    }

    public function attendAction(Request $request, $id, $userId)
    {
        $offlineCourseTask = $this->getOfflineCourseTaskService()->getTask($id);
        if (!$this->getOfflineCourseService()->hasOfflineCourseManageRole($offlineCourseTask['offlineCourseId'])) {
            return $this->createMessageResponse('error', 'offline_course.message.no_permission');
        }

        $userProfiles = $this->getUserService()->getUserProfile($userId);

        $user = $this->getUserService()->getUser($userId);

        $post = $this->getPostService()->getPost($user['postId']);

        $taskResult = $this->getOfflineCourseTaskService()->getTaskResultByTaskIdAndUserId($id, $userId);

        if ($request->isMethod('POST')) {
            $data = $this->buildAttendanceData($id, $userId);
            if (empty($taskResult)) {
                $taskResult = $this->getOfflineCourseTaskService()->createTaskResult($data);
            }

            $attendStatus = $request->request->get('attendStatus');
            if ('unattended' == $attendStatus) {
                $result = $this->getOfflineCourseTaskService()->unattended($taskResult);
            } else {
                $result = $this->getOfflineCourseTaskService()->attended($taskResult);
            }

            return $this->createJsonResponse($result);
        }

        $task = $this->getOfflineCourseTaskService()->getTask($id);
        $isTaskExpire = $task['endTime'] <= time() ? true : false;

        return $this->render('offline-course-manage/attendance-manage/update-modal.html.twig',
            array(
                'user' => $user,
                'userProfiles' => $userProfiles,
                'post' => $post,
                'status' => empty($taskResult) ? 'unattended' : $taskResult['attendStatus'],
                'taskId' => $id,
                'isTaskExpire' => $isTaskExpire,
            )
        );
    }

    public function batchUpdateAction(Request $request, $id)
    {
        $offlineCourseTask = $this->getOfflineCourseTaskService()->getTask($id);
        if (!$this->getOfflineCourseService()->TryManageOfflineCourse($offlineCourseTask['offlineCourseId'])) {
            return $this->createMessageResponse('error', 'offline_course.message.no_permission');
        }

        if ($request->isMethod('POST')) {
            $ids = $request->request->get('ids');

            $ids = explode(',', $ids);
            foreach ($ids as $userId) {
                $data = $this->buildAttendanceData($id, $userId);

                $taskResult = $this->getOfflineCourseTaskService()->getTaskResultByTaskIdAndUserId($id, $userId);
                if (empty($taskResult)) {
                    $taskResult = $this->getOfflineCourseTaskService()->createTaskResult($data);
                }

                $attendStatus = $request->request->get('attendStatus');
                if ('none' == $attendStatus) {
                    $this->getOfflineCourseTaskService()->unattended($taskResult);
                } else {
                    $this->getOfflineCourseTaskService()->attended($taskResult);
                }
            }

            return $this->createJsonResponse(true);
        }

        $task = $this->getOfflineCourseTaskService()->getTask($id);
        $isTaskExpire = $task['endTime'] <= time() ? true : false;

        return $this->render('offline-course-manage/attendance-manage/batch-update-modal.html.twig',
            array(
                'taskId' => $id,
                'isTaskExpire' => $isTaskExpire,
            )
        );
    }

    protected function buildCourseTaskRenderData($request, $id, $taskId, $type = '')
    {
        $attendStatus = $request->cookies->get('attendStatus');
        $offlineCourse = $this->getOfflineCourseService()->tryManageOfflineCourse($id);

        $task = $this->getOfflineCourseTaskService()->getTask($taskId);
        if (empty($task)) {
            throw $this->createNotFoundException('Task does not exist');
        }

        $projectPlanItem = $this->getProjectPlanService()->getProjectPlanItemByTargetIdAndTargetType($offlineCourse['id'], 'offline_course');
        $conditions = $request->request->all();
        if (empty($conditions['attendStatus'])) {
            $conditions['attendStatus'] = $attendStatus;
        }
        $conditions['projectPlanId'] = $projectPlanItem['projectPlanId'];
        $conditions = $this->prepareConditions($conditions, $taskId);
        $count = $this->getProjectPlanMemberService()->countProjectPlanMembers(
            $conditions
        );

        $paginator = new Paginator(
            $request,
            $count,
            20
        );
        if ('ajax' != $type) {
            $paginator->setBaseUrl($this->generateUrl('project_plan_offline_course_attendance_task_list_manage', array('id' => $id, 'taskId' => $taskId)));
        }

        $members = $this->getProjectPlanMemberService()->searchProjectPlanMembers(
            $conditions,
            array('id' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $projectPlanMembers = array();
        foreach ($members as $key => $member) {
            $userProfile = $this->getUserService()->getUserProfile($member['userId']);
            $projectPlanMembers[$key] = array(
                'truename' => $userProfile['truename'],
                'userInfo' => $this->getUserService()->getUser($member['userId']),
                'attendance' => $this->getOfflineCourseTaskService()->getTaskResultByTaskIdAndUserId($taskId, $member['userId']),
            );

            $projectPlanMembers[$key] = $projectPlanMembers[$key] + $member;
        }

        $isTaskExpire = $task['endTime'] <= time() ? true : false;

        return array(
            'offlineCourse' => $offlineCourse,
            'projectPlanMembers' => $projectPlanMembers,
            'paginator' => $paginator,
            'taskId' => $taskId,
            'orgIds' => implode(',', $this->prepareOrgIds($conditions)),
            'projectPlanId' => $projectPlanItem['projectPlanId'],
            'isTaskExpire' => $isTaskExpire,
        );
    }

    protected function buildAttendanceData($taskId, $userId)
    {
        $operatorUser = $this->getUser();

        $data['operatorId'] = $operatorUser['id'];

        $task = $this->getOfflineCourseTaskService()->getTask($taskId);

        $data['offlineCourseId'] = $task['offlineCourseId'];
        $data['taskId'] = $taskId;
        $data['userId'] = $userId;
        if (1 == $task['hasHomework']) {
            $data['homeworkStatus'] = 'unsubmit';
        }

        return $data;
    }

    protected function prepareConditions($conditions, $taskId)
    {
        $members = $this->getProjectPlanMemberService()->findMembersByProjectPlanId($conditions['projectPlanId']);

        $userIds = ArrayToolkit::column($members, 'userId');

        if (isset($conditions['orgIds'])) {
            $conditions['orgIds'] = explode(',', $conditions['orgIds']);
            $users = $this->getUserOrgService()->searchUserOrgs(
                array('orgIds' => $conditions['orgIds'], 'userIds' => $userIds),
                array(),
                0,
                PHP_INT_MAX
            );
            $userIds = ArrayToolkit::column($users, 'userId');
        }

        $conditions['userIds'] = $userIds;

        if (!empty($conditions['username'])) {
            $userIds = $this->getUserService()->findUserIdsByNickNameOrTrueName($conditions['username']);
            $conditions['userIds'] = (empty($conditions['userIds']) || empty($userIds)) ? array() : array_intersect($conditions['userIds'], $userIds);
            unset($conditions['username']);
        }

        $conditions = $this->buildConditionsWithAttendanceStatus($conditions, $taskId);

        $conditions['userIds'] = empty($conditions['userIds']) ? array(-1) : $conditions['userIds'];

        return $conditions;
    }

    protected function prepareOrgIds($conditions)
    {
        if (!isset($conditions['orgIds'])) {
            $orgIds = $this->getOrgService()->findOrgsByPrefixOrgCodes(array('1.'), array('id'));

            return  ArrayToolkit::column($orgIds, 'id');
        }

        return $conditions['orgIds'];
    }

    protected function buildConditionsWithAttendanceStatus($conditions, $taskId)
    {
        if (!empty($conditions['attendStatus'])) {
            if ('all' == $conditions['attendStatus']) {
            } elseif ('attended' == $conditions['attendStatus']) {
                $records = $this->getOfflineCourseTaskService()->searchTaskResults(
                    array('taskId' => $taskId, 'attendStatus' => $conditions['attendStatus']),
                    array(),
                    0,
                    PHP_INT_MAX
                );
                $userIds = ArrayToolkit::column($records, 'userId');

                $conditions['userIds'] = array_intersect($conditions['userIds'], $userIds);
            } elseif ('none' == $conditions['attendStatus'] || 'unattended' == $conditions['attendStatus']) {
                $taskEndTime = $this->getOfflineCourseTaskService()->getTask($taskId)['endTime'];
                if (('unattended' == $conditions['attendStatus'] && $taskEndTime >= time()) || ('none' == $conditions['attendStatus'] && $taskEndTime <= time())) {
                    unset($conditions['userIds']);

                    return $conditions;
                }
                $records = $this->getOfflineCourseTaskService()->searchTaskResults(
                    array('taskId' => $taskId, 'attendStatuses' => array('attended')),
                    array(),
                    0,
                    PHP_INT_MAX
                );
                $otherUserIds = ArrayToolkit::column($records, 'userId');

                $conditions['userIds'] = array_diff($conditions['userIds'], $otherUserIds);
            }

            unset($conditions['attendStatus']);
        }

        return $conditions;
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\Impl\MemberServiceImpl
     */
    protected function getProjectPlanMemberService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:MemberService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineCourse\Service\Impl\TaskServiceImpl
     */
    protected function getOfflineCourseTaskService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineCourse:TaskService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineCourse\Service\Impl\MemberServiceImpl
     */
    protected function getOfflineCourseMemberService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineCourse:MemberService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineCourse\Service\Impl\OfflineCourseServiceImpl
     */
    protected function getOfflineCourseService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineCourse:OfflineCourseService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\User\Service\Impl\UserOrgServiceImpl
     */
    protected function getUserOrgService()
    {
        return $this->createService('CorporateTrainingBundle:User:UserOrgService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\Post\Service\Impl\PostServiceImpl
     */
    protected function getPostService()
    {
        return $this->createService('CorporateTrainingBundle:Post:PostService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\User\Service\Impl\UserServiceImpl
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\Impl\ProjectPlanServiceImpl
     */
    protected function getProjectPlanService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:ProjectPlanService');
    }
}
