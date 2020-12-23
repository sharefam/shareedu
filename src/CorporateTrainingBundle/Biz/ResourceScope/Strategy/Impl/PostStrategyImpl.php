<?php

namespace CorporateTrainingBundle\Biz\ResourceScope\Strategy\Impl;

use AppBundle\Common\ArrayToolkit;
use CorporateTrainingBundle\Biz\ResourceScope\Strategy\BaseStrategy;
use CorporateTrainingBundle\Biz\ResourceScope\Strategy\ResourceScopeStrategy;
use CorporateTrainingBundle\Biz\User\Service\UserService;

class PostStrategyImpl extends BaseStrategy implements ResourceScopeStrategy
{
    public function canVisit($resourceType, $resourceId, $userId)
    {
        $count = $this->getResourceVisibleScopePostDao()->countByResourceTypeAndResourceId($resourceType, $resourceId);

        if ($count > 0) {
            $user = $this->getUserService()->getUser($userId);
            $count = $this->getResourceVisibleScopePostDao()->countByResourceTypeAndResourceIdAndScopes($resourceType, $resourceId, array($user['postId']));

            return $count > 0;
        }

        return true;
    }

    public function canAccess($resourceType, $resourceId, $userId)
    {
        $count = $this->getResourceAccessScopePostDao()->countByResourceTypeAndResourceId($resourceType, $resourceId);

        if ($count > 0) {
            $user = $this->getUserService()->getUser($userId);
            $count = $this->getResourceAccessScopePostDao()->countByResourceTypeAndResourceIdAndScopes($resourceType, $resourceId, array($user['postId']));

            return $count > 0;
        }

        return true;
    }

    public function setResourceVisibleScope($resourceId, $resourceType, $data)
    {
        $publishPosts = array();

        $postIds = array_filter(explode(',', $data));

        foreach ($postIds as $postId) {
            $publishPosts[] = array(
                'resourceId' => $resourceId,
                'resourceType' => $resourceType,
                'scope' => $postId,
                'createdUserId' => $this->getCurrentUser()->getId(),
            );
        }

        $orgVisibleScopes = $this->getResourceVisibleScopePostDao()->findByResourceTypeAndResourceId($resourceType, $resourceId);
        try {
            $this->beginTransaction();
            if (count($orgVisibleScopes) > 0) {
                $this->getResourceVisibleScopePostDao()->batchDelete(array('ids' => ArrayToolkit::column($orgVisibleScopes, 'id')));
            }
            $this->getResourceVisibleScopePostDao()->batchCreate($publishPosts);
            $this->commit();
        } catch (\Exception $e) {
            $this->getLogger()->error('batchCreateVisiblePost:'.$e->getMessage(), $publishPosts);
            $this->rollback();
            throw $e;
        }
    }

    public function setResourceAccessScope($resourceType, $resourceId, $data)
    {
        $accessPosts = array();
        $postIds = array_filter(explode(',', $data));
        foreach ($postIds as $postId) {
            $accessPosts[] = array(
                'resourceId' => $resourceId,
                'resourceType' => $resourceType,
                'scope' => $postId,
                'createdUserId' => $this->getCurrentUser()->getId(),
            );
        }

        $postAccessScopes = $this->getResourceAccessScopePostDao()->findByResourceTypeAndResourceId($resourceType, $resourceId);

        try {
            $this->beginTransaction();
            if (count($postAccessScopes) > 0) {
                $this->getResourceAccessScopePostDao()->batchDelete(array('ids' => ArrayToolkit::column($postAccessScopes, 'id')));
            }
            $this->getResourceAccessScopePostDao()->batchCreate($accessPosts);
            $this->commit();
        } catch (\Exception $e) {
            $this->getLogger()->error('batchCreateAccessPost:'.$e->getMessage(), $accessPosts);
            $this->rollback();
            throw $e;
        }
    }

    public function findVisibleResourceIds($resourceType, $userId, $preResultIds = array())
    {
        $user = $this->getUserService()->getUser($userId);

        if (!empty($preResultIds)) {
            $filteredResourceIds = $this->getResourceVisibleScopePostDao()->findResourceIdsByResourceTypeAndResourceIds($resourceType, $preResultIds);
            if (empty($filteredResourceIds)) {
                return $preResultIds;
            }

            $resourceIdsNotExistInThisScope = array_diff($preResultIds, $filteredResourceIds);
            $resourceIds = $this->getResourceVisibleScopePostDao()->findResourceIdsByResourceTypeAndScopesAndResourceIds($resourceType, array($user['postId']), $filteredResourceIds);
            $resourceIds = array_merge($resourceIds, $resourceIdsNotExistInThisScope);
        } else {
            $resourceIds = $this->getResourceVisibleScopePostDao()->findResourceIdsByResourceTypeAndScopes($resourceType, array($user['postId']));
        }

        return $resourceIds;
    }

    public function findPublicVisibleResourceIds($resourceType, $userId, $preResultIds = array())
    {
        return $this->findVisibleResourceIds($resourceType, $userId, $preResultIds);
    }

    public function findVisibleScopesByResourceTypeAndResourceId($resourceType, $resourceId)
    {
        $scopes = $this->getResourceVisibleScopePostDao()->findByResourceTypeAndResourceId($resourceType, $resourceId);

        return $scopes;
    }

    protected function getResourceVisibleScopePostDao()
    {
        return $this->createDao('ResourceScope:ResourceVisibleScopePostDao');
    }

    public function findAccessScopesByResourceTypeAndResourceId($resourceType, $resourceId)
    {
        $scopes = $this->getResourceAccessScopePostDao()->findByResourceTypeAndResourceId($resourceType, $resourceId);

        return $scopes;
    }

    protected function getResourceAccessScopePostDao()
    {
        return $this->createDao('ResourceScope:ResourceAccessScopePostDao');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
