<?php

namespace CorporateTrainingBundle\Biz\Exporter;

use AppBundle\Common\ArrayToolkit;
use CorporateTrainingBundle\Common\OrgToolkit;
use CorporateTrainingBundle\Biz\ProjectPlan\Service\MemberService;

class ProjectPlanMemberExporter extends AbstractExporter
{
    public function canExport($parameters)
    {
        return $this->getProjectPlanService()->canManageProjectPlan($parameters['projectPlanId']);
    }

    public function getExportFileName()
    {
        return 'project_plan_member.xls';
    }

    public function getSortedHeadingRow($parameters)
    {
        return array(
            array('code' => 'truename', 'title' => $this->trans('student.profile.truename')),
            array('code' => 'nickname', 'title' => $this->trans('student.user_name')),
            array('code' => 'org', 'title' => $this->trans('student.profile.department')),
            array('code' => 'post', 'title' => $this->trans('student.profile.post')),
            array('code' => 'progress', 'title' => $this->trans('project_plan.member.progress')),
            array('code' => 'status', 'title' => $this->trans('project_plan.member.completion')),
        );
    }

    public function buildExportData($parameters)
    {
        $projectPlan = $this->getProjectPlanService()->getProjectPlan($parameters['projectPlanId']);
        $conditions = $this->prepareMemberListSearchConditions($parameters);
        $members = $this->getProjectPlanMemberService()->searchProjectPlanMembers(
            $conditions,
            array('id' => 'DESC'),
            0,
            PHP_INT_MAX
        );

        $userIds = ArrayToolkit::column($members, 'userId');
        list($users, $userProfiles, $orgs, $posts) = $this->buildUsersData($userIds);

        $members = $this->getProjectPlanService()->appendProgress($projectPlan['id'], $members);

        $memberData = array();
        foreach ($members as $member) {
            $memberData[] = array(
                $user = $users[$member['userId']],
                'truename' => empty($userProfiles[$member['userId']]) ? '-' : $userProfiles[$member['userId']]['truename'],
                'nickname' => empty($users[$member['userId']]) ? '-' : $users[$member['userId']]['nickname'],
                'org' => OrgToolkit::buildOrgsNames($user['orgIds'], $orgs),
                'post' => empty($posts[$users[$member['userId']]['postId']]) ? '-' : $posts[$users[$member['userId']]['postId']]['name'],
                'progress' => empty($member['progress']) ? '-' : $member['progress'].'%',
                'status' => (!empty($member['progress']) && 100 == $member['progress']) ? $this->trans('project_plan.status.finished') : $this->trans('project_plan.status.ongoing'),
            );
        }

        $exportData[] = array(
            'data' => $memberData,
        );

        return $exportData;
    }

    protected function prepareMemberListSearchConditions($conditions)
    {
        if (!empty($conditions['username'])) {
            $userIds = $this->getUserService()->findUserIdsByNickNameOrTrueName($conditions['username']);
            if (empty($userIds)) {
                $conditions['userIds'] = array(-1);
            } else {
                $conditions['userIds'] = $userIds;
            }
            unset($conditions['username']);
        }

        return $conditions;
    }

    protected function getAssignmentOrgUserIds($projectPlanId, $orgIds)
    {
        $members = $this->getProjectPlanMemberService()->findMembersByprojectPlanId($projectPlanId);

        $userIds = ArrayToolkit::column($members, 'userId');

        $users = $this->getUserOrgService()->searchUserOrgs(
            array('orgIds' => $orgIds, 'userIds' => $userIds),
            array(),
            0,
            PHP_INT_MAX
        );

        return ArrayToolkit::column($users, 'userId');
    }

    protected function createAssignmentStrategy()
    {
        return $this->biz->offsetGet('projectPlan_item_strategy_context')->createStrategy('course');
    }

    /**
     * @return MemberService
     */
    protected function getProjectPlanMemberService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:MemberService');
    }

    protected function createProjectPlanStrategy()
    {
        return $this->biz->offsetGet('projectPlan_item_strategy_context')->createStrategy('course');
    }
}
