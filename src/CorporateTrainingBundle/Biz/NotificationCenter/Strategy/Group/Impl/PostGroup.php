<?php

namespace CorporateTrainingBundle\Biz\NotificationCenter\Strategy\Group\Impl;

use AppBundle\Common\ArrayToolkit;
use CorporateTrainingBundle\Biz\NotificationCenter\Strategy\Group\Group;
use CorporateTrainingBundle\Biz\NotificationCenter\Strategy\Group\GroupStrategy;

class PostGroup extends GroupStrategy implements Group
{
    public function findGroupEmails($params)
    {
        list($users, $memberNum) = $this->getPostMembers($params);

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
        list($users, $memberNum) = $this->getPostMembers($params);
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

    protected function getPostMembers($params)
    {
        $conditions = array(
            'postId' => $params['postId'],
            'locked' => 0,
        );
        $startNum = $params['startNum'];
        $perPageNum = $params['perPageNum'];

        $memberNum = $this->getUserService()->countUsers($conditions);

        $users = $this->getUserService()->searchUsers(
            $conditions,
            array('id' => 'ASC'),
            $startNum,
            $perPageNum,
            array('id', 'email')
        );

        if (empty($users)) {
            return null;
        }

        return array($users, $memberNum);
    }
}
