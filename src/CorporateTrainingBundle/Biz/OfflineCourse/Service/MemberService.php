<?php

namespace CorporateTrainingBundle\Biz\OfflineCourse\Service;

interface MemberService
{
    public function createMember($member);

    public function updateMember($id, $fields);

    public function deleteMember($id);

    public function getMember($id);

    public function getMemberByOfflineCourseIdAndUserId($offlineCourseId, $userId);

    public function findMembersByIds($ids);

    public function findMembersByOfflineCourseId($offlineCourseId);

    public function findMembersByUserId($userId);

    public function searchMembers($conditions, $orderBys, $start, $limit);

    public function countMembers($conditions);
}
