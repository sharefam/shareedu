<?php

namespace CorporateTrainingBundle\Biz\OfflineCourse\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface TaskDao extends GeneralDaoInterface
{
    public function getByActivityId($activityId);

    public function findByIds($ids);

    public function findByOfflineCourseId($offlineCourseId);

    public function findByOfflineCourseIdAndTypeAndTimeRange($offlineCourseId, $type, $timeRange);

    public function findPersonLearnTimeRankingList($userIds, $courseIds, $timeRange, $limit);

    public function countStartTimeAndEndTimeAllInOfflineCourseTime($timeRange, $courseIds);

    public function countStartTimeEarlierThanSearchStartTimeOfflineCourseTime($timeRange, $courseIds);

    public function countEndTimeLaterThanSearchEndTimeOfflineCourseTime($timeRange, $courseIds);

    public function countStartTimeAndEndTimeAllOutOfflineCourseTime($timeRange, $courseIds);
}
