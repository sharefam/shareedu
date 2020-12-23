<?php

namespace CorporateTrainingBundle\Biz\ResourceScope\Service\Impl;

use Biz\BaseService;
use CorporateTrainingBundle\Biz\ResourceScope\Service\ResourceAccessScopeService;

class ResourceAccessScopeServiceImpl extends BaseService implements ResourceAccessScopeService
{
    public function canUserAccessResource($resourceType, $resourceId, $userId)
    {
        if (!$this->getResourceVisibleScopeService()->canUserVisitResource($resourceType, $resourceId, $userId)) {
            return false;
        }

        $accessScopeTypes = $this->getStrategyContext()->getAccessScopeTypes();
        foreach ($accessScopeTypes as $accessScopeType) {
            $strategy = $this->getStrategyContext()->createStrategy($accessScopeType);
            $canAccess = $strategy->canAccess($resourceType, $resourceId, $userId);
            if (!$canAccess) {
                return false;
            }
        }

        return true;
    }

    public function setResourceAccessScope($resourceId, $resourceType, $data)
    {
        $accessScopeTypes = $this->getStrategyContext()->getAccessScopeTypes();
        try {
            $this->beginTransaction();
            foreach ($accessScopeTypes as $accessScopeType) {
                $accessScopeTypeData = '';
                $strategy = $this->getStrategyContext()->createStrategy($accessScopeType);
                if ($data['conditionalAccess']) {
                    $accessScopeTypeData = $data['access'.$accessScopeType];
                }
                $strategy->setResourceAccessScope($resourceType, $resourceId, $accessScopeTypeData);
            }
            $this->commit();
        } catch (\Exception $e) {
            $this->getLogger()->error('setResourceAccessScope:'.$e->getMessage());
            $this->rollback();
            throw $e;
        }
    }

    protected function getStrategyContext()
    {
        return $this->biz['resource_scope_strategy_context'];
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return ResourceVisibleScopeServiceImpl
     */
    protected function getResourceVisibleScopeService()
    {
        return $this->createService('ResourceScope:ResourceVisibleScopeService');
    }
}
