<?php

namespace CorporateTrainingBundle\Biz\ResourceScope\Service;

interface ResourceVisibleScopeService
{
    public function canUserVisitResource($resourceType, $resourceId, $userId);

    public function findVisibleResourceIdsByResourceTypeAndUserId($resourceType, $userId);

    public function findPublicVisibleResourceIdsByResourceTypeAndUserId($resourceType, $userId);

    public function findDepartmentVisibleResourceIdsByResourceTypeAndUserId($resourceType, $userId);
}
