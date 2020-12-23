<?php

namespace CorporateTrainingBundle\Biz\ProjectPlan\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use CorporateTrainingBundle\Biz\ProjectPlan\Dao\EnrollmentRecordDao;

class EnrollmentRecordDaoImpl extends GeneralDaoImpl implements EnrollmentRecordDao
{
    protected $table = 'project_plan_enrollment_record';

    public function findByProjectPlanId($projectPlanId)
    {
        return $this->findByFields(array(
        'projectPlanId' => $projectPlanId,
        ));
    }

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function getLatestEnrollmentRecordByProjectPlanIdAndUserId($projectPlanId, $userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE projectPlanId = ? AND userId = ? ORDER BY submittedTime DESC limit 1";

        return $this->db()->fetchAssoc($sql, array($projectPlanId, $userId));
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
                'projectPlanId = :projectPlanId',
                'userId IN ( :userIds)',
                'status NOT in ( :excludeStatus )',
                'projectPlanId IN ( :projectPlanIds)',
            ),
        );
    }
}
