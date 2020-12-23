<?php

namespace CorporateTrainingBundle\Biz\ProjectPlan\Service;

interface MemberService
{
    /*
     * member
     */
    public function getProjectPlanMember($id);

    public function getProjectPlanMemberByUserIdAndProjectPlanId($userId, $projectPlanId);

    public function findMembersByIds($ids);

    public function findMembersByUserId($userId);

    public function findMembersByProjectPlanId($projectPlanId);

    public function updateProjectPlanMember($id, $fields);

    public function countProjectPlanMembers(array $conditions);

    public function searchProjectPlanMembers(array $conditions, array $orderBys, $start, $limit, $columns = array());

    public function deleteProjectPlanMember($id);

    public function deleteMemberByProjectPlanId($projectPlanId);

    public function isBelongToUserProjectPlan($userId, $itemId, $type);

    public function batchBecomeMember($projectPlanId, $userIds);

    public function batchDeleteMembers($projectPlanId, $memberIds);

    public function getProjectPlanLearnDataForUserLearnDataExtension($conditions);

    /*
     * enrollmentRecord
     */
    public function attend($projectPlanId, $userId, $recordId, $fields);

    public function createEnrollmentRecord($record);

    public function updateEnrollmentRecord($id, $fields);

    public function getEnrollmentRecord($id);

    public function countEnrollmentRecords(array $conditions);

    public function searchEnrollmentRecords(array $conditions, array $orderBys, $start, $limit);

    public function passProjectPlansApply($recordIds);

    public function rejectProjectPlansApply($recordIds, $info = array());

    public function findEnrollmentRecordsByProjectPlanId($projectPlanId);

    public function findEnrollmentRecordsByIds($ids);

    public function getLatestEnrollmentRecordByProjectPlanIdAndUserId($projectPlanId, $userId);
}
