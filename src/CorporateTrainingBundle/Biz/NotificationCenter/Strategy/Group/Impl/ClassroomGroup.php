<?php

namespace CorporateTrainingBundle\Biz\NotificationCenter\Strategy\Group\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\Classroom\Service\ClassroomService;
use Biz\System\Service\LogService;
use Biz\User\Service\UserService;
use CorporateTrainingBundle\Biz\NotificationCenter\Strategy\Group\Group;
use CorporateTrainingBundle\Biz\NotificationCenter\Strategy\Group\GroupStrategy;

class ClassroomGroup extends GroupStrategy implements Group
{
    public function findGroupEmails($params)
    {
        list($users, $memberNum) = $this->gitClassroomMembers($params);
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
        list($users, $memberNum) = $this->gitClassroomMembers($params);
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

    protected function gitClassroomMembers($params)
    {
        $startNum = $params['startNum'];
        $perPageNum = $params['perPageNum'];

        $members = $this->getClassroomService()->findClassroomStudents($params['classroomId'], $startNum, $perPageNum);
        $memberNum = $this->getClassroomService()->getClassroomStudentCount($params['classroomId']);
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
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }
}
