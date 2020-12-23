<?php

namespace CorporateTrainingBundle\Biz\ResourceScope\Service;

interface ResourceAccessScopeService
{
    public function canUserAccessResource($resourceType, $resourceId, $userId);
}
