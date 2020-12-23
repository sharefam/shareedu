<?php

namespace CorporateTrainingBundle\Controller\ProjectPlan\Item;

use AppBundle\Controller\BaseController;

class BaseItemController extends BaseController
{
    public function hasManageRole()
    {
        if (!$this->getProjectPlanService()->hasManageProjectPlanPermission()) {
            return $this->createMessageResponse('error', 'project_plan.message.can_not_manage_message');
        }
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\Impl\ProjectPlanServiceImpl
     */
    protected function getProjectPlanService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:ProjectPlanService');
    }
}
