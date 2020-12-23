<?php

namespace CorporateTrainingBundle\Biz\OfflineActivity\Service;

interface EnrollmentRecordService
{
    public function createEnrollmentRecord($record);

    public function updateEnrollmentRecord($id, $fields);

    public function getEnrollmentRecord($id);

    public function countEnrollmentRecords(array $conditions);

    public function searchEnrollmentRecords(array $conditions, array $orderBys, $start, $limit);

    public function getLatestEnrollmentRecordByActivityIdAndUserId($activityId, $userId);

    public function passOfflineActivityApply($recordId);

    public function rejectOfflineActivityApply($recordId, $info = array());

    public function batchPass($recordIds);

    public function batchReject($recordIds, $info = array());

    public function calculateOfflineActivitySubmittedStudentNum($activityId);

    public function findEnrollmentRecordsByActivityId($activityId);

    public function findEnrollmentRecordsByIds($ids);

    public function deleteEnrollmentRecordByActivityIdAndUserId($activityId, $userId);
}
