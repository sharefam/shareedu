<?php

namespace CorporateTrainingBundle\Biz\ProjectPlan\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use CorporateTrainingBundle\Biz\ProjectPlan\Dao\AdvancedOptionDao;

class AdvancedOptionDaoImpl extends GeneralDaoImpl implements AdvancedOptionDao
{
    protected $table = 'project_plan_advanced_options';

    public function getByProjectPlanId($projectPlanId)
    {
        return $this->getByFields(array(
            'projectPlanId' => $projectPlanId,
        ));
    }

    public function declares()
    {
        return array(
            'orderbys' => array('id'),
            'conditions' => array(
                'id = :id',
                'requireRemark = :requireRemark',
                'requireData = :requireData',
                'projectPlanId = :projectPlanId',
            ),
        );
    }
}
