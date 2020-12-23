<?php

namespace CorporateTrainingBundle\Biz\UserGroup\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use CorporateTrainingBundle\Biz\UserGroup\Service\MemberService;

class MemberServiceImpl extends BaseService implements MemberService
{
    public function addUserGroupMember($userGroupMember)
    {
        return $this->getUserGroupMemberDao()->create($userGroupMember);
    }

    public function deleteUserGroupMemberByGroupId($groupId)
    {
        return $this->getUserGroupMemberDao()->deleteByGroupId($groupId);
    }

    public function deleteUserGroupMember($id)
    {
        return $this->getUserGroupMemberDao()->delete($id);
    }

    public function deleteUserGroupMemberByMemberIdAndMemberType($memberId, $memberType)
    {
        return $this->getUserGroupMemberDao()->deleteByMemberIdAndMemberType($memberId, $memberType);
    }

    public function countUserGroupMemberByGroupId($groupId)
    {
        $userIds = $this->findDistinctUserIdsByGroupId($groupId);

        if (empty($userIds)) {
            return 0;
        }

        return count($userIds);
    }

    public function countUserGroupMemberByGroupIdAndOrgIds($groupId, $orgIds)
    {
        $userIds = $this->findDistinctUserIdsByGroupId($groupId);
        if (empty($userIds)) {
            return 0;
        }
        $userOrgs = $this->getUserOrgService()->searchUserOrgs(
            array('userIds' => $userIds, 'orgIds' => $orgIds),
            array(),
            0,
            PHP_INT_MAX
        );
        $userIds = ArrayToolkit::column($userOrgs, 'userId');

        return count(array_unique($userIds));
    }

    public function getUserGroupMember($id)
    {
        return $this->getUserGroupMemberDao()->get($id);
    }

    public function getUserGroupMemberByGroupIdAndMemberIdAndMemberType($groupId, $memberId, $memberType)
    {
        return $this->getUserGroupMemberDao()->getByGroupIdAndMemberIdAndMemberType($groupId, $memberId, $memberType);
    }

    public function findUserGroupMembersByGroupId($groupId)
    {
        return $this->getUserGroupMemberDao()->findByGroupId($groupId);
    }

    public function findUserGroupMembersByGroupIdAndMemberType($groupId, $memberType)
    {
        return $this->getUserGroupMemberDao()->findByGroupIdAndMemberType($groupId, $memberType);
    }

    public function findUserGroupMembersByMemberIdAndMemberType($memberId, $memberType)
    {
        return $this->getUserGroupMemberDao()->findByMemberIdAndMemberType($memberId, $memberType);
    }

    public function searchUserGroupMembers($conditions, $orderBy, $start, $limit)
    {
        return $this->getUserGroupMemberDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function countUserGroupMembers($conditions)
    {
        return $this->getUserGroupMemberDao()->count($conditions);
    }

    public function countUserGroupMembersByMemberTypeAndMemberId($memberType, $memberId)
    {
        if ($memberType == 'org') {
            $userOrgs = $this->getUserOrgService()->searchUserOrgs(array('orgId' => $memberId), array(), 0, PHP_INT_MAX);
            $userIds = ArrayToolkit::column($userOrgs, 'userId');
            if (empty($userIds)) {
                return 0;
            }

            return $this->getUserService()->countUsers(array('userIds' => $userIds, 'locked' => 0, 'noType' => 'system'));
        }
        if ($memberType == 'post') {
            return $this->getUserService()->countUsers(array('postId' => $memberId, 'locked' => 0));
        }
    }

    public function isMemberExistInUserGroup($groupId, $memberId, $memberType)
    {
        $result = false;
        $userGroupMember = $this->getUserGroupMemberDao()->getByGroupIdAndMemberIdAndMemberType($groupId, $memberId, $memberType);
        if (empty($userGroupMember)) {
            return $result;
        } elseif ($userGroupMember['memberId'] == $memberId) {
            $result = true;
        }

        return $result;
    }

    public function becomeMemberByImport($groupId, $userId)
    {
        $userGroup = $this->getUserGroupService()->getUserGroup($groupId);
        if (empty($userGroup)) {
            throw $this->createNotFoundException("UserGroup #{$userGroup} not found");
        }

        $user = $this->getUserService()->getUser($userId);

        if (empty($user)) {
            throw  $this->createNotFoundException("User #{$userId} not found");
        }

        $member = $this->getUserGroupMemberByGroupIdAndMemberIdAndMemberType($groupId, $userId, 'user');
        if ($member) {
            throw  $this->createServiceException("Member #{$member['id']} has exist");
        }

        $fields['groupId'] = $groupId;
        $fields['memberId'] = $userId;
        $fields['memberType'] = 'user';

        $this->getLogService()->info(
            'userGroup',
            'import_user_group_member',
            "import user {$userId} into user_group {$groupId}"
        );

        return $this->addUserGroupMember($fields);
    }

    public function findDistinctUserIdsByGroupId($groupId)
    {
        $members = $this->findUserGroupMembersByGroupId($groupId);
        $attributes = array();

        foreach ($members as $member) {
            $attributes[] = array(
                'attributeType' => $member['memberType'],
                'attributeId' => $member['memberId'],
            );
        }

        return $this->getUserAttributeService()->findDistinctUserIdsByAttributes($attributes);
    }

    public function findUserGroupsByUserId($userId)
    {
        $user = $this->getUserService()->getUser($userId);

        $userGroupMembers = $this->getUserGroupMemberDao()->findByMemberIdAndMemberType($userId, 'user');
        $userGroupIds = ArrayToolkit::column($userGroupMembers, 'groupId');

        $userOrgIds = $user['orgIds'];
        $orgUserGroupMembers = array();
        if (!empty($userOrgIds)) {
            foreach ($userOrgIds as $userOrgId) {
                $orgUserGroupMembers = array_merge($orgUserGroupMembers, $this->getUserGroupMemberDao()->findByMemberIdAndMemberType($userOrgId, 'org'));
            }
        }
        $orgUserGroupIds = ArrayToolkit::column($orgUserGroupMembers, 'groupId');

        $postUserGroupMembers = array();
        if (!$user['postId']) {
            $postUserGroupMembers = $this->getUserGroupMemberDao()->findByMemberIdAndMemberType($user['postId'], 'post');
        }
        $postUserGroupIds = ArrayToolkit::column($postUserGroupMembers, 'groupId');

        $groupIds = array_unique(array_merge($userGroupIds, $orgUserGroupIds, $postUserGroupIds));

        $groups = $this->getUserGroupService()->findUserGroupsByIds(array_values($groupIds));

        return $groups;
    }

    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    protected function getUserService()
    {
        return $this->createService('CorporateTrainingBundle:User:UserService');
    }

    protected function getUserOrgService()
    {
        return $this->createService('CorporateTrainingBundle:User:UserOrgService');
    }

    protected function getUserGroupService()
    {
        return $this->createService('CorporateTrainingBundle:UserGroup:UserGroupService');
    }

    protected function getUserGroupMemberDao()
    {
        return $this->createDao('CorporateTrainingBundle:UserGroup:MemberDao');
    }

    protected function getUserAttributeService()
    {
        return $this->createService('CorporateTrainingBundle:UserAttribute:UserAttributeService');
    }
}
