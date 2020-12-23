<?php

namespace CorporateTrainingBundle\Biz\NotificationCenter\Strategy\Group\Impl;

use AppBundle\Common\ArrayToolkit;
use CorporateTrainingBundle\Biz\NotificationCenter\Strategy\Group\Group;
use CorporateTrainingBundle\Biz\NotificationCenter\Strategy\Group\GroupStrategy;

class UserGroup extends GroupStrategy implements Group
{
    public function findGroupEmails($params)
    {
        list($users, $userNum) = $this->getUsers($params);

        $emails = ArrayToolkit::column($users, 'email');

        $params['startNum'] += $params['perPageNum'];

        $group = array(
            'emails' => $emails,
            'params' => $params,
            'num' => $userNum,
        );

        return $group;
    }

    public function findGroupDingTalkUsers($params)
    {
        $params['perPageNum'] = 100; //钉钉接口限制了彼此最大用户数
        list($users, $userNum) = $this->getUsers($params);

        $ids = ArrayToolkit::column($users, 'id');

        $params['startNum'] += $params['perPageNum'];

        $group = array(
            'ids' => $ids,
            'params' => $params,
            'num' => $userNum,
        );

        return $group;
    }

    protected function getUsers($params)
    {
        $userIds = $params['userIds'];
        $startNum = $params['startNum'];
        $perPageNum = $params['perPageNum'];
        $userNum = count($userIds);

        $userIds = array_slice($userIds, $startNum, $perPageNum);
        $users = $this->getUserService()->findUsersByIds($userIds);

        return array($users, $userNum);
    }

    public function findGroupMobiles($params)
    {
        // TODO: Implement findGroupMobiles() method.
    }
}
