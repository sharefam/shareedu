<?php

namespace CorporateTrainingBundle\Biz\Group\Dao\Impl;

use Biz\Group\Dao\Impl\ThreadDaoImpl as BaseDaoImpl;

class ThreadDaoImpl extends BaseDaoImpl
{
    public function countGroupPostNumDataByUserIdsAndDate($userIds, $date)
    {
        $userMarks = str_repeat('?,', count($userIds) - 1).'?';

        $sql = "SELECT userId, COUNT(userId) as totalGroupPostNum FROM {$this->table} WHERE userId IN ({$userMarks}) AND (createdTime >= ? AND createdTime <= ?) GROUP BY userId";

        $parameters = array_merge($userIds, array_values($date));

        return $this->db()->fetchAll($sql, $parameters);
    }

    public function declares()
    {
        $declares = parent::declares();
        array_push($declares['conditions'], 'groupId IN (:groupIds)');

        return $declares;
    }
}
