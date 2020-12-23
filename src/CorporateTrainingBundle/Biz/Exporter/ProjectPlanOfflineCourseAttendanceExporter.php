<?php

namespace CorporateTrainingBundle\Biz\Exporter;

use AppBundle\Common\ArrayToolkit;
use CorporateTrainingBundle\Common\OrgToolkit;

class ProjectPlanOfflineCourseAttendanceExporter extends AbstractExporter
{
    public function canExport($parameters)
    {
        $offlineCourseTask = $this->getOfflineCourseTaskService()->getTask($parameters['taskId']);
        if ($this->getOfflineCourseService()->tryManageOfflineCourse($offlineCourseTask['offlineCourseId'])) {
            return true;
        }
    }

    public function getExportFileName()
    {
        return 'project_plan_offline_course_attendance.xls';
    }

    public function getSortedHeadingRow($parameters)
    {
        return array(
            array('code' => 'truename', 'title' => $this->trans('student.profile.truename')),
            array('code' => 'nickname', 'title' => $this->trans('student.user_name')),
            array('code' => 'org', 'title' => $this->trans('student.profile.department')),
            array('code' => 'post', 'title' => $this->trans('student.profile.post')),
            array('code' => 'status', 'title' => $this->trans('offline_activity.attend_status')),
        );
    }

    public function buildExportData($parameters)
    {
        $conditions = $this->prepareConditions($parameters, $parameters['taskId']);
        $taskEndTime = $this->getOfflineCourseTaskService()->getTask($parameters['taskId'])['endTime'];

        $members = $this->getProjectPlanMemberService()->searchProjectPlanMembers(
            $conditions,
            array('id' => 'DESC'),
            0,
            PHP_INT_MAX
        );

        $userIds = ArrayToolkit::column($members, 'userId');
        list($users, $userProfiles, $orgs, $posts) = $this->buildUsersData($userIds);

        $attendance = $this->getOfflineCourseTaskService()->searchTaskResults(
            array('taskId' => $parameters['taskId'], 'userIds' => $userIds),
            array(),
            0,
            PHP_INT_MAX
        );
        $attendance = ArrayToolkit::index($attendance, 'userId');

        $memberData = array();
        foreach ($members as $member) {
            $memberData[] = array(
                $user = $users[$member['userId']],
                'truename' => empty($userProfiles[$member['userId']]) ? '-' : $userProfiles[$member['userId']]['truename'],
                'nickname' => empty($users[$member['userId']]) ? '-' : $users[$member['userId']]['nickname'],
                'org' => OrgToolkit::buildOrgsNames($user['orgIds'], $orgs),
                'post' => empty($posts[$users[$member['userId']]['postId']]) ? '-' : $posts[$users[$member['userId']]['postId']]['name'],
                'status' => $this->getAttendanceStatus($attendance, $member, $taskEndTime),
            );
        }

        $exportData[] = array(
            'data' => $memberData,
        );

        return $exportData;
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

    protected function findUserIdsByOrgIds($orgIds)
    {
        $usersOrg = $this->getUserOrgService()->findUserOrgsByOrgIds($orgIds);

        return ArrayToolkit::column($usersOrg, 'userId');
    }

    protected function prepareOrgIds($conditions)
    {
        if (isset($conditions['orgIds']) && empty($conditions['orgIds'])) {
            $conditions['orgIds'] = array(-1);

            return $conditions;
        }

        return explode(',', $conditions['orgIds']);
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

    protected function getAttendanceStatus($attendance, $member, $taskEndTime)
    {
        if (isset($attendance[$member['userId']])) {
            if ($attendance[$member['userId']]['attendStatus'] == 'attended') {
                return $this->trans('project_plan.status.registered');
            } elseif ($attendance[$member['userId']]['attendStatus'] == 'none') {
                if ($taskEndTime <= time()) {
                    return $this->trans('project_plan.status.absenteeism');
                } else {
                    return $this->trans('project_plan.status.unattend');
                }
            }
        } else {
            return $this->trans('project_plan.status.unattend');
        }
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
     * @return \CorporateTrainingBundle\Biz\OfflineCourse\Service\Impl\OfflineCourseServiceImpl
     */
    protected function getOfflineCourseService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineCourse:OfflineCourseService');
    }
}
