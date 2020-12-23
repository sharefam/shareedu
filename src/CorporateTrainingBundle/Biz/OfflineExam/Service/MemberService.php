<?php

namespace CorporateTrainingBundle\Biz\OfflineExam\Service;

interface MemberService
{
    public function createMember($member);

    public function updateMember($id, $fields);

    public function deleteMember($id);

    public function getMember($id);

    public function getMemberByOfflineExamIdAndUserId($offlineExamId, $userId);

    public function findMembersByIds($ids);

    public function findMembersByOfflineExamId($offlineExamId);

    public function searchMembers($conditions, $orderBys, $start, $limit);

    public function countMembers($conditions);
}
