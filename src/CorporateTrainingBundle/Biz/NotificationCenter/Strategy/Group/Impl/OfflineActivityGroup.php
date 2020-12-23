<?php

namespace CorporateTrainingBundle\Biz\NotificationCenter\Strategy\Group\Impl;

use AppBundle\Common\ArrayToolkit;
use CorporateTrainingBundle\Biz\NotificationCenter\Strategy\Group\Group;
use CorporateTrainingBundle\Biz\NotificationCenter\Strategy\Group\GroupStrategy;
use CorporateTrainingBundle\Biz\OfflineActivity\Service\MemberService;
use CorporateTrainingBundle\Biz\OfflineActivity\Service\OfflineActivityService;

class OfflineActivityGroup extends GroupStrategy implements Group
{
    public function findGroupEmails($params)
    {
        list($users, $memberNum) = $this->getOfflineActivityMembers($params);
        $emails = ArrayToolkit::column($users, 'email');

        $params['startNum'] += $params['perPageNum'];

        $group = array(
            'emails' => $emails,
            'params' => $params,
            'num' => $memberNum,
        );

        return $group;
    }

    public function findGroupDingTalkUsers($params)
    {
        $params['perPageNum'] = 100; //钉钉接口限制了彼此最大用户数
        list($users, $memberNum) = $this->getOfflineActivityMembers($params);
        $ids = ArrayToolkit::column($users, 'id');

        $params['startNum'] += $params['perPageNum'];

        $group = array(
            'ids' => $ids,
            'params' => $params,
            'num' => $memberNum,
        );

        return $group;
    }

    public function findGroupMobiles($params)
    {
        // TODO: Implement findGroupMobiles() method.
    }

    protected function getOfflineActivityMembers($params)
    {
        $conditions = array(
            'offlineActivityId' => $params['offlineActivityId'],
        );
        $startNum = $params['startNum'];
        $perPageNum = $params['perPageNum'];

        $members = $this->getMemberService()->searchMembers(
            $conditions,
            array('id' => 'ASC'),
            $startNum,
            $perPageNum
        );

        $memberNum = $this->getMemberService()->countMembers($conditions);

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
     * @return OfflineActivityService
     */
    protected function getOfflineActivityService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineActivity:OfflineActivity');
    }

    /**
     * @return MemberService
     */
    protected function getMemberService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineActivity:MemberService');
    }
}
