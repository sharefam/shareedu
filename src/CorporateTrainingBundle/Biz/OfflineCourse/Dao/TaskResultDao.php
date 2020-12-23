<?php

namespace CorporateTrainingBundle\Biz\OfflineCourse\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface TaskResultDao extends GeneralDaoInterface
{
    public function getByTaskIdAndUserId($taskId, $userId);

    public function findByIds($ids);

    public function findByOfflineCourseId($offlineCourseId);

    public function findByUserId($userId);

    public function deleteByTaskId($taskId);

    public function findHomeworkStatusNumGroupByStatus($taskId, $userIds);

    public function calculateUsersOfflineLearnTime($userIds, $offlineCourseIds, $endDateRange);
}
