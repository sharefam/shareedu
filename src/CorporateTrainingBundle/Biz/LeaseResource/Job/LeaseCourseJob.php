<?php

namespace CorporateTrainingBundle\Biz\LeaseResource\Job;

use Biz\LeaseResource\Service\ResourcePlatformService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class LeaseCourseJob extends AbstractJob
{
    public function execute()
    {
        $resourceCode = $this->args['resourceCode'];

        $this->getResourcePlatformService()->leaseProductInfo('course', $resourceCode);
    }

    /**
     * @return ResourcePlatformService
     */
    protected function getResourcePlatformService()
    {
        return $this->biz->service('LeaseResource:ResourcePlatformService');
    }
}
