<?php

namespace CorporateTrainingBundle\Biz\Task\Dao\Impl;

use Biz\Task\Dao\Impl\TaskDaoImpl as BaseDaoImpl;
use CorporateTrainingBundle\Biz\Task\Dao\TaskDao;

class TaskDaoImpl extends BaseDaoImpl implements TaskDao
{
    public function findByCourseIdAndTaskTypeAndTimeRange($courseId, $type, $time)
    {
        $sql = "SELECT * FROM {$this->table} WHERE courseId = ? AND type = ? AND ((? > startTime AND endTime > ?) OR (? <= startTime AND startTime <= ?) OR (? <= endTime AND endTime <= ?))";

        return $this->db()->fetchAll($sql, array($courseId, $type, $time['startTime'], $time['endTime'], $time['startTime'], $time['endTime'], $time['startTime'], $time['endTime']));
    }

    public function getByActivityId($activityId)
    {
        return $this->getByFields(array('activityId' => $activityId));
    }

    public function declares()
    {
        $declares = parent::declares();
        array_push($declares['conditions'], 'activityId IN (:activityIds)');

        return $declares;
    }
}
