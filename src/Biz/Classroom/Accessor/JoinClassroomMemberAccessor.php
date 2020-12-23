<?php

namespace Biz\Classroom\Accessor;

use Biz\Accessor\AccessorAdapter;
use Biz\Classroom\Service\ClassroomService;
use CorporateTrainingBundle\Biz\ResourceScope\Service\ResourceAccessScopeService;

class JoinClassroomMemberAccessor extends AccessorAdapter
{
    public function access($classroom)
    {
        $user = $this->getCurrentUser();
        if (null === $user || !$user->isLogin()) {
            return $this->buildResult('user.not_login');
        }

        if ($user['locked']) {
            return $this->buildResult('user.locked', array('userId' => $user['id']));
        }

        if (!$this->getResourceAccessScopeService()->canUserAccessResource('classroom', $classroom['id'], $user['id']) && $classroom['conditionalAccess'] == 1) {
            return $this->buildResult('resource.can_not_access', array('userId' => $user['id']));
        }
        
        $member = $this->getClassroomService()->getClassroomMember($classroom['id'], $user['id']);
        if (empty($member) || $member['role'] == array('auditor')) {
            return null;
        }

        return $this->buildResult('member.member_exist', array('userId' => $user['id']));
    }

    /**
     * @return ResourceAccessScopeService
     */
    protected function getResourceAccessScopeService()
    {
        return $this->biz->service('CorporateTrainingBundle:ResourceScope:ResourceAccessScopeService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->biz->service('Classroom:ClassroomService');
    }
}
