<?php

namespace CorporateTrainingBundle\Biz\ResourceScope\Strategy\Impl;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Exception\AccessDeniedException;
use CorporateTrainingBundle\Biz\ManagePermission\Service\ManagePermissionOrgService;
use CorporateTrainingBundle\Biz\Org\Service\Impl\OrgServiceImpl;
use CorporateTrainingBundle\Biz\ResourceScope\Dao\ResourceAccessScopeOrgDao;
use CorporateTrainingBundle\Biz\ResourceScope\Strategy\BaseStrategy;
use CorporateTrainingBundle\Biz\ResourceScope\Strategy\ResourceScopeStrategy;

class OrgStrategyImpl extends BaseStrategy implements ResourceScopeStrategy
{
    public function canVisit($resourceType, $resourceId, $userId)
    {
        $user = $this->getUserService()->getUserWithOrgScopes($userId);
        $count = $this->getResourceVisibleScopeOrgDao()->countByResourceTypeAndResourceIdAndScopes($resourceType, $resourceId, $user['lineOrgIds']);

        return $count > 0;
    }

    public function canAccess($resourceType, $resourceId, $userId)
    {
        $user = $this->getUserService()->getUserWithOrgScopes($userId);
        $count = $this->getResourceAccessScopeOrgDao()->countByResourceTypeAndResourceIdAndScopes($resourceType, $resourceId, $user['lineOrgIds']);

        return $count > 0;
    }

    public function setResourceVisibleScope($resourceId, $resourceType, $data)
    {
        $publishOrgs = array();

        $orgIds = array_filter(explode(',', $data));

        $orgVisibleScopes = $this->getResourceVisibleScopeOrgDao()->findByResourceTypeAndResourceId($resourceType, $resourceId);
        $oldSettingOrgIds = ArrayToolkit::column($orgVisibleScopes, 'scope');

        if (!$this->getManagePermissionOrgService()->checkOrgManagePermission($orgIds, $oldSettingOrgIds)) {
            throw new AccessDeniedException('admin.manage.org_permission_beyond_error');
        }
        $orgIds = $this->getOrgService()->wipeOffChildrenOrgIds($orgIds);

        foreach ($orgIds as $orgId) {
            $publishOrgs[] = array(
                'resourceId' => $resourceId,
                'resourceType' => $resourceType,
                'scope' => $orgId,
                'createdUserId' => $this->getCurrentUser()->getId(),
            );
        }
        try {
            $this->beginTransaction();
            if (count($orgVisibleScopes) > 0) {
                $this->getResourceVisibleScopeOrgDao()->batchDelete(array('ids' => ArrayToolkit::column($orgVisibleScopes, 'id')));
            }
            $this->getResourceVisibleScopeOrgDao()->batchCreate($publishOrgs);
            $this->commit();
        } catch (\Exception $e) {
            $this->getLogger()->error('batchCreateVisibleOrg:'.$e->getMessage(), $publishOrgs);
            $this->rollback();
            throw $e;
        }
    }

    public function setResourceAccessScope($resourceType, $resourceId, $data)
    {
        $accessOrgs = array();
        $orgIds = array_filter(explode(',', $data));
        $orgAccessScopes = $this->getResourceAccessScopeOrgDao()->findByResourceTypeAndResourceId($resourceType, $resourceId);
        $oldSettingOrgIds = ArrayToolkit::column($orgAccessScopes, 'scope');

        if (!$this->getManagePermissionOrgService()->checkOrgManagePermission($orgIds, $oldSettingOrgIds)) {
            throw new AccessDeniedException('admin.manage.org_permission_beyond_error');
        }
        $orgIds = $this->getOrgService()->wipeOffChildrenOrgIds($orgIds);
        foreach ($orgIds as $orgId) {
            $accessOrgs[] = array(
                'resourceId' => $resourceId,
                'resourceType' => $resourceType,
                'scope' => $orgId,
                'createdUserId' => $this->getCurrentUser()->getId(),
            );
        }
        try {
            $this->beginTransaction();
            if (count($orgAccessScopes) > 0) {
                $this->getResourceAccessScopeOrgDao()->batchDelete(array('ids' => ArrayToolkit::column($orgAccessScopes, 'id')));
            }

            $this->getResourceAccessScopeOrgDao()->batchCreate($accessOrgs);
            $this->commit();
        } catch (\Exception $e) {
            $this->getLogger()->error('batchCreateAccessOrg:'.$e->getMessage(), $accessOrgs);
            $this->rollback();
            throw $e;
        }
    }

    public function findVisibleResourceIds($resourceType, $userId, $preResultIds = array())
    {
        $user = $this->getUserService()->getUserWithOrgScopes($userId);

        $resourceIds = $this->getResourceVisibleScopeOrgDao()->findResourceIdsByResourceTypeAndScopes($resourceType, $user['lineOrgIds']);

        return $resourceIds;
    }

    public function findPublicVisibleResourceIds($resourceType, $userId, $preResultIds = array())
    {
        $resourceIds = $this->getResourceVisibleScopeOrgDao()->findResourceIdsByResourceTypeAndScope($resourceType, 1);

        return $resourceIds;
    }

    public function findVisibleResourceIdsByResourceTypeAndScope($resourceType, $scope = 1)
    {
        $resourceIds = $this->getResourceVisibleScopeOrgDao()->findResourceIdsByResourceTypeAndScope($resourceType, $scope);

        return $resourceIds;
    }

    public function findVisibleScopesByResourceTypeAndResourceId($resourceType, $resourceId)
    {
        $scopes = $this->getResourceVisibleScopeOrgDao()->findByResourceTypeAndResourceId($resourceType, $resourceId);

        return $scopes;
    }

    public function findAccessResourceIds($resourceType, $userId)
    {
        // TODO: Implement findAccessResourceIds() method.
        return array();
    }

    public function findAccessScopesByResourceTypeAndResourceId($resourceType, $resourceId)
    {
        $scopes = $this->getResourceAccessScopeOrgDao()->findByResourceTypeAndResourceId($resourceType, $resourceId);

        return $scopes;
    }

    /**
     * @return ManagePermissionOrgService
     */
    protected function getManagePermissionOrgService()
    {
        return $this->createService('CorporateTrainingBundle:ManagePermission:ManagePermissionOrgService');
    }

    protected function getResourceVisibleScopeOrgDao()
    {
        return $this->createDao('ResourceScope:ResourceVisibleScopeOrgDao');
    }

    /**
     * @return ResourceAccessScopeOrgDao
     */
    protected function getResourceAccessScopeOrgDao()
    {
        return $this->createDao('ResourceScope:ResourceAccessScopeOrgDao');
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return OrgServiceImpl
     */
    protected function getOrgService()
    {
        return $this->createService('CorporateTrainingBundle:Org:OrgService');
    }
}
