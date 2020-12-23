<?php

namespace CorporateTrainingBundle\Biz\NotificationCenter\Strategy\Group\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\MemberService;
use Biz\System\Service\LogService;
use Biz\User\Service\UserService;
use CorporateTrainingBundle\Biz\NotificationCenter\Strategy\Group\Group;
use CorporateTrainingBundle\Biz\NotificationCenter\Strategy\Group\GroupStrategy;

class CourseGroup extends GroupStrategy implements Group
{
    public function findGroupEmails($params)
    {
        list($users, $memberNum) = $this->gitCourseMembers($params);
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
        list($users, $memberNum) = $this->gitCourseMembers($params);
        $ids = empty($users) ? array(-1) : ArrayToolkit::column($users, 'id');

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

    protected function gitCourseMembers($params)
    {
        $conditions = array(
            'courseId' => $params['courseId'],
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
     * @return \CorporateTrainingBundle\Biz\User\Service\UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return MemberService
     */
    protected function getMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }
}
