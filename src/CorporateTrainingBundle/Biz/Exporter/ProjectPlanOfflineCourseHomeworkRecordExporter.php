<?php

namespace CorporateTrainingBundle\Biz\Exporter;

use AppBundle\Common\ArrayToolkit;
use CorporateTrainingBundle\Common\OrgToolkit;

class ProjectPlanOfflineCourseHomeworkRecordExporter extends AbstractExporter
{
    public function canExport($parameters)
    {
        if ($this->getOfflineCourseService()->tryManageOfflineCourse($parameters['offlineCourseId'])) {
            return true;
        }
    }

    public function getExportFileName()
    {
        return 'project_plan_offline_course_homework_record.xls';
    }

    public function getSortedHeadingRow($parameters)
    {
        return array(
            array('code' => 'truename', 'title' => $this->trans('student.profile.truename')),
            array('code' => 'nickname', 'title' => $this->trans('student.user_name')),
            array('code' => 'org', 'title' => $this->trans('student.profile.department')),
            array('code' => 'post', 'title' => $this->trans('student.profile.post')),
            array('code' => 'homeworkStatus', 'title' => $this->trans('offline_course.homework_status')),
        );
    }

    public function buildExportData($parameters)
    {
        $conditions = $this->prepareConditions($parameters);

        $taskResults = $this->getOfflineCourseTaskService()->searchTaskResults(
            array('taskId' => $parameters['taskId'], 'userIds' => $conditions['userIds']),
            array('createdTime' => 'DESC'),
            0,
            PHP_INT_MAX
        );

        $userIds = ArrayToolkit::column($taskResults, 'userId');
        list($users, $userProfiles, $orgs, $posts) = $this->buildUsersData($userIds);

        $taskResults = ArrayToolkit::index($taskResults, 'userId');

        $memberData = array();
        foreach ($taskResults as $taskResult) {
            $memberData[] = array(
                $user = $users[$taskResult['userId']],
                'truename' => empty($userProfiles[$taskResult['userId']]) ? '-' : $userProfiles[$taskResult['userId']]['truename'],
                'nickname' => empty($users[$taskResult['userId']]) ? '-' : $users[$taskResult['userId']]['nickname'],
                'org' => OrgToolkit::buildOrgsNames($user['orgIds'], $orgs),
                'post' => empty($posts[$users[$taskResult['userId']]['postId']]) ? '-' : $posts[$users[$taskResult['userId']]['postId']]['name'],
                'homeworkStatus' => $this->getHomeworkStatus($taskResult),
            );
        }

        $exportData[] = array(
            'data' => $memberData,
        );

        return $exportData;
    }

    protected function prepareConditions($conditions)
    {
        if (isset($conditions['homeworkStatus']) && 'all' == $conditions['homeworkStatus']) {
            unset($conditions['homeworkStatus']);
        }

        if (isset($conditions['orgIds'])) {
            $orgUserIds = $this->getUserOrgService()->searchUserOrgs(
                array('orgIds' => explode(',', $conditions['orgIds'])),
                array(),
                0,
                PHP_INT_MAX
            );
            $conditions['userIds'] = ArrayToolkit::column($orgUserIds, 'userId');
        }

        if (!empty($conditions['username'])) {
            $userIds = $this->getUserService()->findUserIdsByNickNameOrTrueName($conditions['username']);
            $conditions['userIds'] = (empty($userIds) || empty($conditions['userIds'])) ? array() : array_intersect($conditions['userIds'], $userIds);
        }

        $conditions['userIds'] = empty($conditions['userIds']) ? array(-1) : $conditions['userIds'];

        return $conditions;
    }

    protected function getProjectPlanOrgUserIds($projectPlanId, $orgIds)
    {
        $members = $this->getProjectPlanMemberService()->findMembersByProjectPlanId($projectPlanId);

        $userIds = ArrayToolkit::column($members, 'userId');

        $users = $this->getUserOrgService()->searchUserOrgs(
            array('orgIds' => $orgIds, 'userIds' => $userIds),
            array(),
            0,
            PHP_INT_MAX
        );

        return ArrayToolkit::column($users, 'userId');
    }

    protected function getHomeworkStatus($taskResult)
    {
        if ('submitted' == $taskResult['homeworkStatus']) {
            return $this->trans('project_plan.implementation.course_no_review');
        } elseif ('passed' == $taskResult['homeworkStatus']) {
            return $this->trans('project_plan.pass');
        } else {
            return $this->trans('project_plan.status.unpass');
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
