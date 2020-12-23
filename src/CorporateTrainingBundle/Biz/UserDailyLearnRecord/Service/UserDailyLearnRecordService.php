<?php

namespace CorporateTrainingBundle\Biz\UserDailyLearnRecord\Service;

interface UserDailyLearnRecordService
{
    public function getDailyRecord($id);

    public function createDailyRecord(array $dailyRecord);

    public function createCurrentDateDailyRecord(array $dailyRecord);

    public function updateDailyRecord($id, array $fields);

    public function countRecords(array $conditions);

    public function searchRecords(array $conditions, array $orderBys, $start, $limit);

    public function getRecordByUserIdAndCourseId($userId, $courseId);

    public function getTodayRecordByUserIdAndCourseId($userId, $courseId);

    public function getRecordByUserIdAndCourseIdAndCourseStatus($userId, $courseId, $status);

    public function getRecordByUserIdAndCourseIdAndDate($userId, $courseId, $date);

    public function waveLearnTimeById($id, $learnTime);

    public function waveFinishedTaskNumById($id);

    public function sumLearnTimeByUserId($userId);

    public function sumPostLearnTimeByUserIdAndPostId($userId, $postId);

    public function sumLearnTimeByConditions(array $conditions);

    public function countFinishedCourseNumByConditions(array $conditions);
}
