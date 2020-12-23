<?php

namespace CorporateTrainingBundle\Biz\AdvancedMemberSelect;

use AppBundle\Common\ArrayToolkit;

class ProjectPlanMemberSelect extends AbstractMemberSelect
{
    protected $resourceType = 'project_plan';

    public function canSelect($targetId)
    {
        if (empty($targetId)) {
            return false;
        }

        return $this->getProjectPlanService()->canManageProjectPlan($targetId);
    }

    public function becomeMember($targetId, $userIds)
    {
        if (empty($userIds)) {
            return true;
        }

        $result = $this->getProjectPlanMemberService()->batchBecomeMember($targetId, $userIds);

        return $result;
    }

    protected function filterMembers($targetId, $userIds)
    {
        $members = $this->getProjectPlanMemberService()->searchProjectPlanMembers(array('userIds' => $userIds, 'projectPlanId' => $targetId), array(), 0, count($userIds), array('userId'));
        $existUserIds = ArrayToolkit::column($members, 'userId');

        return array_diff($userIds, $existUserIds);
    }

    protected function sendNotification($userIds, $targetId)
    {
        if (!empty($this->mailNotification['enroll'])) {
            $types[] = 'email';
        }

        if (!empty($this->dingtalkNotification['project_plan_assign'])) {
            $types[] = 'dingtalk';
        }
        if (empty($types)) {
            return;
        }

        global $kernel;
        $url = $kernel->getContainer()->get('router')->generate('project_plan_detail', array('id' => $targetId), true);
        $projectPlan = $this->getProjectPlanService()->getProjectPlan($targetId);
        $to = array(
            'type' => 'user',
            'userIds' => $userIds,
            'startNum' => 0,
            'perPageNum' => 20,
        );
        $content = array(
            'template' => 'project_plan_assign',
            'params' => array(
                'targetId' => $projectPlan['id'],
                'batch' => 'project_plan_assign'.$projectPlan['id'].time(),
                'projectPlanName' => $projectPlan['name'],
                'imagePath' => empty($projectPlan['cover']['large']) ? '' : $projectPlan['cover']['large'],
                'url' => $url,
            ),
        );

        $this->biz->offsetGet('notification_default')->send($types, $to, $content);
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\Impl\ProjectPlanServiceImpl
     */
    protected function getProjectPlanService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:ProjectPlanService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\Impl\MemberServiceImpl
     */
    protected function getProjectPlanMemberService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:MemberService');
    }
}
