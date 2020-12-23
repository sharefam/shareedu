<?php

namespace CorporateTrainingBundle\Biz\NotificationCenter\Strategy\Group\Impl;

use AppBundle\Common\ArrayToolkit;
use CorporateTrainingBundle\Biz\NotificationCenter\Strategy\Group\Group;
use CorporateTrainingBundle\Biz\NotificationCenter\Strategy\Group\GroupStrategy;
use CorporateTrainingBundle\Biz\ProjectPlan\Service\MemberService;
use CorporateTrainingBundle\Biz\ProjectPlan\Service\ProjectPlanService;

class ProjectPlanGroup extends GroupStrategy implements Group
{
    public function findGroupEmails($params)
    {
        list($users, $memberNum) = $this->getProjectPlanMembers($params);

        $emails = ArrayToolkit::column($users, 'email');

        $params['startNum'] += $params['perPageNum'];

        return array(
            'emails' => $emails,
            'params' => $params,
            'num' => $memberNum,
        );
    }

    public function findGroupDingTalkUsers($params)
    {
        $params['perPageNum'] = 100; //钉钉接口限制了彼此最大用户数
        list($users, $memberNum) = $this->getProjectPlanMembers($params);
        $ids = ArrayToolkit::column($users, 'id');

        $params['startNum'] += $params['perPageNum'];

        return array(
            'ids' => $ids,
            'params' => $params,
            'num' => $memberNum,
        );
    }

    public function findGroupMobiles($params)
    {
        // TODO: Implement findGroupMobiles() method.
    }

    protected function getProjectPlanMembers($params)
    {
        $conditions = array(
            'projectPlanId' => $params['projectPlanId'],
        );
        $startNum = $params['startNum'];
        $perPageNum = $params['perPageNum'];

        $members = $this->getMemberService()->searchProjectPlanMembers(
            $conditions,
            array('id' => 'ASC'),
            $startNum,
            $perPageNum
        );

        $memberNum = $this->getMemberService()->countProjectPlanMembers($conditions);

        if (empty($members)) {
            return null;
        }

        $users = $this->getUserService()->searchUsers(
            array(
                'userIds' => ArrayToolkit::column($members, 'userId'),
                'locked' => 0,
            ),
            array('id' => 'ASC'),
            0,
            PHP_INT_MAX,
            array('id', 'email')
        );

        return array($users, $memberNum);
    }

    /**
     * @return ProjectPlanService
     */
    protected function getProjectPlanService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:ProjectPlanService');
    }

    /**
     * @return MemberService
     */
    protected function getMemberService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:MemberService');
    }
}
