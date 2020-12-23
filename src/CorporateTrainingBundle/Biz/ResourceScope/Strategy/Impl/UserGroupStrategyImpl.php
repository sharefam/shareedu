<?php

namespace CorporateTrainingBundle\Biz\ResourceScope\Strategy\Impl;

use AppBundle\Common\ArrayToolkit;
use CorporateTrainingBundle\Biz\ResourceScope\Strategy\BaseStrategy;
use CorporateTrainingBundle\Biz\ResourceScope\Strategy\ResourceScopeStrategy;
use CorporateTrainingBundle\Biz\UserGroup\Service\Impl\MemberServiceImpl;

class UserGroupStrategyImpl extends BaseStrategy implements ResourceScopeStrategy
{
    public function canVisit($resourceType, $resourceId, $userId)
    {
        $count = $this->getResourceVisibleScopeUserGroupDao()->countByResourceTypeAndResourceId($resourceType, $resourceId);

        if ($count > 0) {
            $userGroups = $this->getUserGroupMemberService()->findUserGroupsByUserId($userId);
            $userGroupIds = ArrayToolkit::column($userGroups, 'id');
            $count = $this->getResourceVisibleScopeUserGroupDao()->countByResourceTypeAndResourceIdAndScopes($resourceType, $resourceId, $userGroupIds);

            return $count > 0;
        }

        return true;
    }

    public function canAccess($resourceType, $resourceId, $userId)
    {
        $count = $this->getResourceAccessScopeUserGroupDao()->countByResourceTypeAndResourceId($resourceType, $resourceId);

        if ($count > 0) {
            $userGroups = $this->getUserGroupMemberService()->findUserGroupsByUserId($userId);
            $userGroupIds = ArrayToolkit::column($userGroups, 'id');
            $count = $this->getResourceAccessScopeUserGroupDao()->countByResourceTypeAndResourceIdAndScopes($resourceType, $resourceId, $userGroupIds);

            return $count > 0;
        }

        return true;
    }

    public function setResourceVisibleScope($resourceId, $resourceType, $data)
    {
        $publishUserGroups = array();
        $userGroupIds = array_filter(explode(',', $data));
        foreach ($userGroupIds as $userGroupId) {
            $publishUserGroups[] = array(
                'resourceId' => $resourceId,
                'resourceType' => $resourceType,
                'scope' => $userGroupId,
                'createdUserId' => $this->getCurrentUser()->getId(),
            );
        }

        $userGroupVisibleScopes = $this->getResourceVisibleScopeUserGroupDao()->findByResourceTypeAndResourceId($resourceType, $resourceId);
        try {
            $this->beginTransaction();
            if (count($userGroupVisibleScopes) > 0) {
                $this->getResourceVisibleScopeUserGroupDao()->batchDelete(array('ids' => ArrayToolkit::column($userGroupVisibleScopes, 'id')));
            }
            $this->getResourceVisibleScopeUserGroupDao()->batchCreate($publishUserGroups);
            $this->commit();
        } catch (\Exception $e) {
            $this->getLogger()->error('batchCreateVisibleUserGroup:'.$e->getMessage(), $publishUserGroups);
            $this->rollback();
            throw $e;
        }
    }

    public function setResourceAccessScope($resourceType, $resourceId, $data)
    {
        $accessUserGroups = array();
        $userGroupIds = array_filter(explode(',', $data));
        foreach ($userGroupIds as $userGroupId) {
            $accessUserGroups[] = array(
                'resourceId' => $resourceId,
                'resourceType' => $resourceType,
                'scope' => $userGroupId,
                'createdUserId' => $this->getCurrentUser()->getId(),
            );
        }

        $userGroupAccessScopes = $this->getResourceAccessScopeUserGroupDao()->findByResourceTypeAndResourceId($resourceType, $resourceId);

        try {
            $this->beginTransaction();
            if (count($userGroupAccessScopes) > 0) {
                $this->getResourceAccessScopeUserGroupDao()->batchDelete(array('ids' => ArrayToolkit::column($userGroupAccessScopes, 'id')));
            }
            $this->getResourceAccessScopeUserGroupDao()->batchCreate($accessUserGroups);
            $this->commit();
        } catch (\Exception $e) {
            $this->getLogger()->error('batchCreateAccessUserGroup:'.$e->getMessage(), $accessUserGroups);
            $this->rollback();
            throw $e;
        }
    }

    public function findVisibleResourceIds($resourceType, $userId, $preResultIds = array())
    {
        $userGroups = $this->getUserGroupMemberService()->findUserGroupsByUserId($userId);
        $userGroupIds = ArrayToolkit::column($userGroups, 'id');

        if (!empty($preResultIds)) {
            $filteredResourceIds = $this->getResourceVisibleScopeUserGroupDao()->findResourceIdsByResourceTypeAndResourceIds($resourceType, $preResultIds);
            if (empty($filteredResourceIds)) {
                return $preResultIds;
            }

            $resourceIdsNotExistInThisScope = array_diff($preResultIds, $filteredResourceIds);
            $resourceIds = $this->getResourceVisibleScopeUserGroupDao()->findResourceIdsByResourceTypeAndScopesAndResourceIds($resourceType, $userGroupIds, $filteredResourceIds);
            $resourceIds = array_merge($resourceIds, $resourceIdsNotExistInThisScope);
        } else {
            $resourceIds = $this->getResourceVisibleScopeUserGroupDao()->findResourceIdsByResourceTypeAndScopes($resourceType, $userGroupIds);
        }

        return $resourceIds;
    }

    public function findPublicVisibleResourceIds($resourceType, $userId, $preResultIds = array())
    {
        return $this->findVisibleResourceIds($resourceType, $userId, $preResultIds);
    }

    public function findVisibleScopesByResourceTypeAndResourceId($resourceType, $resourceId)
    {
        $scopes = $this->getResourceVisibleScopeUserGroupDao()->findByResourceTypeAndResourceId($resourceType, $resourceId);

        return $scopes;
    }

    public function findAccessScopesByResourceTypeAndResourceId($resourceType, $resourceId)
    {
        $scopes = $this->getResourceAccessScopeUserGroupDao()->findByResourceTypeAndResourceId($resourceType, $resourceId);

        return $scopes;
    }

    protected function getResourceVisibleScopeUserGroupDao()
    {
        return $this->createDao('ResourceScope:ResourceVisibleScopeUserGroupDao');
    }

    protected function getResourceAccessScopeUserGroupDao()
    {
        return $this->createDao('ResourceScope:ResourceAccessScopeUserGroupDao');
    }

    /**
     * @return MemberServiceImpl
     */
    protected function getUserGroupMemberService()
    {
        return $this->createService('CorporateTrainingBundle:UserGroup:MemberService');
    }
}
