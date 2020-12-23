<?php

namespace CorporateTrainingBundle\Biz\OfflineActivity\Service;

interface MemberService
{
    public function createMember($member);

    public function updateMember($id, $fields);

    public function deleteMember($id);

    public function getMember($memberId);

    public function countMembers($conditions);

    public function searchMembers(array $conditions, array $orderBys, $start, $limit, $columns = array());

    public function findMembersByOfflineActivityId($activityId);

    public function getMemberByActivityIdAndUserId($activityId, $userId);

    public function becomeMember($activityId, $userId);

    public function batchBecomeMember($activityId, $userIds);

    public function enter($activityId, $userId);

    public function becomeMemberByImport($activityId, $userId);

    public function isMember($activityId, $userId);

    public function attendMember($id, $attendedStatus);

    public function signIn($id);

    public function gradeMember($id, $fields);

    public function removeMember($id);

    public function statisticMembersAttendStatusByActivityId($activityId);

    public function statisticMemberPassStatusByActivityId($activityId);

    public function statisticMemberScoreByActivityId($activityId);

    public function findDistinctUserIdsByDate($startTime, $endTime);

    public function getOfflineActivityLearnDataForUserLearnDataExtension($conditions);
}
