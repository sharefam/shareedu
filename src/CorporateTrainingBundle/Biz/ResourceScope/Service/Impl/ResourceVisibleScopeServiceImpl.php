<?php

namespace CorporateTrainingBundle\Biz\ResourceScope\Service\Impl;

use Biz\BaseService;
use CorporateTrainingBundle\Biz\Org\Service\OrgService;
use CorporateTrainingBundle\Biz\ResourceScope\Dao\ResourceVisibleScopeOrgDao;
use CorporateTrainingBundle\Biz\ResourceScope\Dao\ResourceVisibleScopePostDao;
use CorporateTrainingBundle\Biz\ResourceScope\Dao\ResourceVisibleScopeUserGroupDao;
use CorporateTrainingBundle\Biz\ResourceScope\Service\ResourceVisibleScopeService;

class ResourceVisibleScopeServiceImpl extends BaseService implements ResourceVisibleScopeService
{
    public function canUserVisitResource($resourceType, $resourceId, $userId)
    {
        $accessScopeTypes = $this->getStrategyContext()->getAccessScopeTypes();
        foreach ($accessScopeTypes as $accessScopeType) {
            $strategy = $this->getStrategyContext()->createStrategy($accessScopeType);
            $canVisit = $strategy->canVisit($resourceType, $resourceId, $userId);

            if (!$canVisit) {
                return false;
            }
        }

        return true;
    }

    public function findVisibleResourceIdsByResourceTypeAndUserId($resourceType, $userId)
    {
        $resourceIds = array();
        $visibleScopeTypes = $this->getStrategyContext()->getVisibleScopeTypes();
        foreach ($visibleScopeTypes as $visibleScopeType) {
            $strategy = $this->getStrategyContext()->createStrategy($visibleScopeType);
            $resourceIds = $strategy->findVisibleResourceIds($resourceType, $userId, $resourceIds);
        }

        return empty($resourceIds) ? array(-1) : $resourceIds;
    }

    public function findPublicVisibleResourceIdsByResourceTypeAndUserId($resourceType, $userId)
    {
        $resourceIds = array();
        $visibleScopeTypes = $this->getStrategyContext()->getVisibleScopeTypes();
        foreach ($visibleScopeTypes as $visibleScopeType) {
            $strategy = $this->getStrategyContext()->createStrategy($visibleScopeType);
            $resourceIds = $strategy->findPublicVisibleResourceIds($resourceType, $userId, $resourceIds);
        }

        return empty($resourceIds) ? array(-1) : $resourceIds;
    }

    public function findDepartmentVisibleResourceIdsByResourceTypeAndUserId($resourceType, $userId)
    {
        $PublicVisibleResourceIds = $this->findPublicVisibleResourceIdsByResourceTypeAndUserId($resourceType, $userId);
        $visibleResourceIds = $this->findVisibleResourceIdsByResourceTypeAndUserId($resourceType, $userId);
        $visibleResourceIds = array_diff($visibleResourceIds, $PublicVisibleResourceIds);

        return empty($visibleResourceIds) ? array(-1) : $visibleResourceIds;
    }

    public function setResourceVisibleScope($resourceId, $resourceType, $data)
    {
        $visibleScopeTypes = $this->getStrategyContext()->getVisibleScopeTypes();
        try {
            $this->beginTransaction();

            foreach ($visibleScopeTypes as $visibleScopeType) {
                $visibleScopeData = '';
                $strategy = $this->getStrategyContext()->createStrategy($visibleScopeType);
                if ($data['showable']) {
                    $visibleScopeData = empty($data['publish'.$visibleScopeType]) ? '' : $data['publish'.$visibleScopeType];
                }
                $strategy->setResourceVisibleScope($resourceId, $resourceType, $visibleScopeData);
            }
            $this->commit();
        } catch (\Exception $e) {
            $this->getLogger()->error('setResourceVisibleScope:'.$e->getMessage());
            $this->rollback();
            throw $e;
        }
    }

    /**
     * @return ResourceVisibleScopeOrgDao
     */
    protected function getResourceVisibleScopeOrgDao()
    {
        return $this->createDao('CorporateTrainingBundle:ResourceScope:ResourceVisibleScopeOrgDao');
    }

    /**
     * @return ResourceVisibleScopePostDao
     */
    protected function getResourceVisibleScopePostDao()
    {
        return $this->createDao('CorporateTrainingBundle:ResourceScope:ResourceVisibleScopePostDao');
    }

    /**
     * @return ResourceVisibleScopeUserGroupDao
     */
    protected function getResourceVisibleScopeUserGroupDao()
    {
        return $this->createDao('CorporateTrainingBundle:ResourceScope:ResourceVisibleScopeUserGroupDao');
    }

    /**
     * @return OrgService
     */
    protected function getOrgService()
    {
        return $this->createService('CorporateTrainingBundle:Org:OrgService');
    }

    protected function getStrategyContext()
    {
        return $this->biz['resource_scope_strategy_context'];
    }
}
