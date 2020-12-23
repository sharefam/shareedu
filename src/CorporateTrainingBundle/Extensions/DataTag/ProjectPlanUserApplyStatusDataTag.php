<?php

namespace CorporateTrainingBundle\Extensions\DataTag;

use AppBundle\Extensions\DataTag\BaseDataTag;
use AppBundle\Extensions\DataTag\DataTag;

class ProjectPlanUserApplyStatusDataTag extends BaseDataTag implements DataTag
{
    public function getData(array $arguments)
    {
        return $this->getProjectPlanService()->getUserApplyStatus(
            $arguments['projectPlanId'],
            $arguments['userId']
        );
    }

    protected function getProjectPlanService()
    {
        return $this->getServiceKernel()->getBiz()->service(
            'CorporateTrainingBundle:ProjectPlan:ProjectPlanService'
        );
    }
}
