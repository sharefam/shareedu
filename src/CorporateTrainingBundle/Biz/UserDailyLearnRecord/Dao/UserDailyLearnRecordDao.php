<?php

namespace CorporateTrainingBundle\Biz\UserDailyLearnRecord\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface UserDailyLearnRecordDao extends GeneralDaoInterface
{
    public function getByUserIdAndCourseId($userId, $courseId);

    public function getByUserIdAndCourseIdAndCourseStatus($userId, $courseId, $courseStatus);

    public function waveLearnTimeById($id, $learnTime);

    public function waveFinishedTaskNum($id, $finishedTaskNum);

    public function getByUserIdAndCourseIdAndDate($userId, $courseId, $date);

    public function sumLearnTimeByUserId($userId);

    public function sumPostLearnTimeByUserIdAndPostId($userId, $postId);

    public function sumLearnTimeByConditions($conditions);

    public function calculateLearnDataByUserIdsAndDate(array $userIds, $date);

    public function calculateStudyHoursByUserIdsAndDate(array $userIds, $date);

    public function calculatePostCourseLearnDataByUserIdsAndDate(array $userIds, $date);

    public function statisticsLearnRecordGroupByOrgId(array $conditions);

    public function statisticsLearnRecordGroupByPostId(array $conditions);

    public function statisticsLearnRecordGroupByCategoryId(array $conditions);

    public function statisticsOrgLearnTimeRankingList(array $conditions, $start = 0, $limit = 5);

    public function statisticsLearnUsersNumGroupByDate(array $conditions);

    public function statisticsTotalLearnTimeGroupByDate(array $conditions);
}
