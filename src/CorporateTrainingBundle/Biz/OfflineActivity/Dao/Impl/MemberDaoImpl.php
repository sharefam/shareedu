<?php

namespace CorporateTrainingBundle\Biz\OfflineActivity\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use CorporateTrainingBundle\Biz\OfflineActivity\Dao\MemberDao;

class MemberDaoImpl extends GeneralDaoImpl implements MemberDao
{
    protected $table = 'offline_activity_member';

    public function findByActivityId($activityId)
    {
        return $this->findByFields(array(
            'offlineActivityId' => $activityId,
        ));
    }

    public function getByActivityIdAndUserId($activityId, $userId)
    {
        return $this->getByFields(
            array(
                'offlineActivityId' => $activityId,
                'userId' => $userId,
            )
        );
    }

    public function findDistinctUserIdsByCreatedTime($startTime, $endTime)
    {
        $sql = "SELECT DISTINCT userId FROM {$this->table} WHERE createdTime >= ? AND createdTime <= ? ";

        return $this->db()->fetchAll($sql, array($startTime, $endTime));
    }

    public function statisticAttendStatusByActivityId($activityId)
    {
        $sql = "SELECT attendedStatus, COUNT(id) AS count FROM  {$this->table}  WHERE offlineActivityId = ? GROUP BY attendedStatus";

        return $this->db()->fetchAll($sql, array($activityId));
    }

    public function statisticPassStatusByActivityId($activityId)
    {
        $sql = "SELECT passedStatus, COUNT(id) AS count FROM  {$this->table}  WHERE offlineActivityId = ? GROUP BY passedStatus";

        return $this->db()->fetchAll($sql, array($activityId));
    }

    public function statisticScoreByActivityId($activityId)
    {
        $sql = "SELECT sum(CASE WHEN score <= 10 AND score >= 0 THEN 1 ELSE 0 END) AS '0-10' ,
                       sum(CASE WHEN score <= 20 AND score > 10 THEN 1 ELSE 0 END) AS '11-20', 
                       sum(CASE WHEN score <= 30 AND score > 20 THEN 1 ELSE 0 END) AS '21-30',
                       sum(CASE WHEN score <= 40 AND score > 30 THEN 1 ELSE 0 END) AS '31-40',
                       sum(CASE WHEN score <= 50 AND score > 40 THEN 1 ELSE 0 END) AS '41-50',
                       sum(CASE WHEN score <= 60 AND score > 50 THEN 1 ELSE 0 END) AS '51-60',
                       sum(CASE WHEN score <= 70 AND score > 60 THEN 1 ELSE 0 END) AS '61-70',
                       sum(CASE WHEN score <= 80 AND score > 70 THEN 1 ELSE 0 END) AS '71-80',
                       sum(CASE WHEN score <= 90 AND score > 80 THEN 1 ELSE 0 END) AS '81-90',
                       sum(CASE WHEN score <= 100 AND score > 90  THEN 1 ELSE 0 END) AS '91-100'       
                      FROM {$this->table} WHERE offlineActivityId = ? AND passedStatus IN ('passed', 'unpassed')";

        return $this->db()->fetchAll($sql, array($activityId));
    }

    public function calculateActivityDataByUserIdsAndDate($userIds, $date)
    {
        $userMarks = str_repeat('?,', count($userIds) - 1).'?';

        $sql = "SELECT m.userId, COUNT(DISTINCT m.offlineActivityId) as learnedOfflineActivityNum, COUNT(IF(m.passedStatus = 'passed',true,null)) AS finishedOfflineActivityNum FROM `offline_activity_member` m LEFT JOIN `offline_activity` a ON m.offlineActivityId = a.id AND m.userId IN ({$userMarks}) WHERE (a.startTime >= ? AND a.startTime <= ?) OR (a.endTime >= ? AND a.endTime <= ?) OR (a.startTime <= ? AND a.endTime >= ?) GROUP BY m.userId";

        $parameters = array_merge($userIds, array_values($date), array_values($date), array_values($date));

        return $this->db()->fetchAll($sql, $parameters);
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'orderbys' => array(
                'id',
                'score',
                'createdTime',
                'updatedTime',
            ),
            'conditions' => array(
                'userId = :userId',
                'joinStatus = :joinStatus',
                'offlineActivityId = :activityId',
                'passedStatus = :passedStatus',
                'userId IN ( :userIds)',
                'offlineActivityId IN ( :offlineActivityIds)',
                'createdTime <= :createdTime_LE',
                'createdTime >= :createdTime_GE',
            ),
        );
    }
}
