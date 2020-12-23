<?php

namespace CorporateTrainingBundle\Biz\UserGroup\Service;

interface MemberService
{
    public function addUserGroupMember($userGroupMember);

    public function deleteUserGroupMemberByGroupId($groupId);

    public function deleteUserGroupMemberByMemberIdAndMemberType($memberId, $memberType);

    public function getUserGroupMemberByGroupIdAndMemberIdAndMemberType($groupId, $memberId, $memberType);

    public function countUserGroupMemberByGroupId($groupId);

    public function countUserGroupMemberByGroupIdAndOrgIds($groupId, $orgIds);

    public function findUserGroupMembersByGroupId($groupId);

    public function findUserGroupMembersByMemberIdAndMemberType($memberId, $memberType);

    public function searchUserGroupMembers($conditions, $orderBy, $start, $limit);

    public function countUserGroupMembers($conditions);

    public function isMemberExistInUserGroup($groupId, $memberId, $memberType);

    public function becomeMemberByImport($groupId, $userId);

    public function findDistinctUserIdsByGroupId($groupId);

    public function findUserGroupsByUserId($userId);
}
