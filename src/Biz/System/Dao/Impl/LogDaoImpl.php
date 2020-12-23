<?php

namespace Biz\System\Dao\Impl;

use Biz\System\Dao\LogDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class LogDaoImpl extends GeneralDaoImpl implements LogDao
{
    protected $table = 'log';

    public function declares()
    {
        return array(
            'orderbys' => array(
                'createdTime',
                'id',
            ),
            'conditions' => array(
                'module = :module',
                'action = :action',
                'level = :level',
                'userId = :userId',
                'createdTime > :startDateTime',
                'createdTime < :endDateTime',
                'createdTime >= :startDateTime_GE',
                'userId IN ( :userIds )',
            ),
        );
    }

    public function analysisLoginNumByTime($startTime, $endTime)
    {
        $sql = "SELECT count(distinct userid)  as num FROM `{$this->table}` WHERE `action`='login_success' AND  `createdTime`>= ? AND `createdTime`<= ?  ";

        return $this->db()->fetchColumn($sql, array($startTime, $endTime));
    }

    public function analysisLoginDataByTime($startTime, $endTime)
    {
        $sql = "SELECT count(distinct userid) as count, from_unixtime(createdTime,'%Y-%m-%d') as date FROM `{$this->table}` WHERE `action`='login_success' AND `createdTime`>= ? AND `createdTime`<= ? group by from_unixtime(`createdTime`,'%Y-%m-%d') order by date ASC ";

        return $this->db()->fetchAll($sql, array($startTime, $endTime));
    }

    public function analysisWebDateHourLoginDataByUserIds($date, $userIds)
    {
        $userMarks = str_repeat('?,', count($userIds) - 1).'?';

        $sql = "SELECT HOUR(FROM_UNIXTIME(createdTime)) AS hour, count(userId) as count FROM `{$this->table}` WHERE `action`='login_success' AND userId IN ({$userMarks}) AND FROM_UNIXTIME(createdTime, '%Y-%m-%d') = ? GROUP BY HOUR(FROM_UNIXTIME(createdTime)) ORDER BY HOUR (FROM_UNIXTIME(createdTime))";

        $parameters = array_merge($userIds, array($date));

        return $this->db()->fetchAll($sql, $parameters);
    }

    public function analysisAppDateHourLoginDataByUserIds($date, $userIds)
    {
        $userMarks = str_repeat('?,', count($userIds) - 1).'?';

        $sql = "SELECT HOUR(FROM_UNIXTIME(createdTime)) AS hour, count(userId) as count FROM `{$this->table}` WHERE `module`='mobile' AND `action`='user_login' AND userId IN ({$userMarks}) AND FROM_UNIXTIME(createdTime, '%Y-%m-%d') = ? GROUP BY HOUR(FROM_UNIXTIME(createdTime)) ORDER BY HOUR (FROM_UNIXTIME(createdTime))";

        $parameters = array_merge($userIds, array($date));

        return $this->db()->fetchAll($sql, $parameters);
    }
}
