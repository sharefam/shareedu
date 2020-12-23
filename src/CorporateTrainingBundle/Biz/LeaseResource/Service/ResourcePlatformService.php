<?php

namespace CorporateTrainingBundle\Biz\LeaseResource\Service;

interface ResourcePlatformService
{
    public function leaseProductInfo($resourceType, $resourceCode);
}
