<?php

namespace CorporateTrainingBundle\Biz\OfflineActivity\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface MemberDao extends GeneralDaoInterface
{
    public function findByActivityId($activityId);

    public function getByActivityIdAndUserId($activityId, $userId);

    public function statisticAttendStatusByActivityId($activityId);

    public function statisticPassStatusByActivityId($activityId);

    public function statisticScoreByActivityId($activityId);

    public function findDistinctUserIdsByCreatedTime($startTime, $endTime);

    public function calculateActivityDataByUserIdsAndDate($userIds, $date);
}
