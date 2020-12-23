<?php

namespace CorporateTrainingBundle\Biz\ProjectPlan\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface AdvancedOptionDao extends GeneralDaoInterface
{
    public function getByProjectPlanId($projectPlanId);
}
