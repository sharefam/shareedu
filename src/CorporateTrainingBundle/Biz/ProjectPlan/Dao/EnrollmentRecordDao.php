<?php

namespace CorporateTrainingBundle\Biz\ProjectPlan\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface EnrollmentRecordDao extends GeneralDaoInterface
{
    public function findByProjectPlanId($projectPlanId);

    public function findByIds($ids);

    public function getLatestEnrollmentRecordByProjectPlanIdAndUserId($projectPlanId, $userId);
}
