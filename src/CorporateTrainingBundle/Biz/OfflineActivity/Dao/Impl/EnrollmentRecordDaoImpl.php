<?php

namespace CorporateTrainingBundle\Biz\OfflineActivity\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use CorporateTrainingBundle\Biz\OfflineActivity\Dao\EnrollmentRecordDao;

class EnrollmentRecordDaoImpl extends GeneralDaoImpl implements EnrollmentRecordDao
{
    protected $table = 'offline_activity_enrollment_record';

    public function getLatestByActivityIdAndUserId($activityId, $userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE offlineActivityId = ? AND userId = ? ORDER BY submittedTime DESC limit 1";

        return $this->db()->fetchAssoc($sql, array($activityId, $userId));
    }

    public function calculateSubmittedStudentNumByActivityId($activityId)
    {
        $sql = "SELECT count(distinct userId) FROM {$this->table} WHERE offlineActivityId = ? AND status = 'submitted'";

        return $this->db()->fetchColumn($sql, array($activityId));
    }

    public function findByActivityId($activityId)
    {
        return $this->findByFields(
            array(
                'offlineActivityId' => $activityId,
            )
        );
    }

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function deleteByActivityIdAndUserId($activityId, $userId)
    {
        return $this->db()->delete($this->table, array('offlineActivityId' => $activityId, 'userId' => $userId));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('submittedTime', 'updatedTime'),
            'serializes' => array(),
            'orderbys' => array('id', 'submittedTime', 'approvedTime', 'updatedTime'),
            'conditions' => array(
                'status = :status',
                'userId = :userId',
                'offlineActivityId = :offlineActivityId',
                'userId IN ( :userIds)',
                'offlineActivityId IN ( :offlineActivityIds)',
            ),
        );
    }
}
