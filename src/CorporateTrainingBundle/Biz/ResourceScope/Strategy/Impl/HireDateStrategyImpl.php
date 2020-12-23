<?php

namespace CorporateTrainingBundle\Biz\ResourceScope\Strategy\Impl;

use AppBundle\Common\ArrayToolkit;
use CorporateTrainingBundle\Biz\ResourceScope\Dao\ResourceAccessScopeHireDateDao;
use CorporateTrainingBundle\Biz\ResourceScope\Strategy\BaseStrategy;
use CorporateTrainingBundle\Biz\ResourceScope\Strategy\ResourceScopeStrategy;

class HireDateStrategyImpl extends BaseStrategy implements ResourceScopeStrategy
{
    public function canVisit($resourceType, $resourceId, $userId)
    {
        return true;
    }

    public function canAccess($resourceType, $resourceId, $userId)
    {
        $hireDateAccessScopes = $this->getResourceAccessScopeHireDateDao()->findByResourceTypeAndResourceId($resourceType, $resourceId);
        $user = $this->getUserService()->getUser($userId);

        if (!empty($hireDateAccessScopes)) {
            if (empty($user['hireDate'])) {
                return false;
            }

            $hireDateAccessScope = array_shift($hireDateAccessScopes);
            $result = $this->isSatisfyHireDateRange($hireDateAccessScope['scope'], $user['hireDate']);

            return $result;
        }

        return true;
    }

    public function setResourceVisibleScope($resourceType, $resourceId, $data)
    {
    }

    public function setResourceAccessScope($resourceType, $resourceId, $data)
    {
        $accessHireDates = array();
        $accessHireDates[] = array(
            'resourceId' => $resourceId,
            'resourceType' => $resourceType,
            'scope' => $data,
            'createdUserId' => $this->getCurrentUser()->getId(),
        );

        $hireDateAccessScopes = $this->getResourceAccessScopeHireDateDao()->findByResourceTypeAndResourceId($resourceType, $resourceId);

        try {
            $this->beginTransaction();
            if (count($hireDateAccessScopes) > 0) {
                $this->getResourceAccessScopeHireDateDao()->batchDelete(array('ids' => ArrayToolkit::column($hireDateAccessScopes, 'id')));
            }
            $accessHireDate = array_shift($accessHireDates);

            if (is_array($accessHireDate['scope']) && count($accessHireDate['scope']) >= 2) {
                $this->getResourceAccessScopeHireDateDao()->batchCreate(array($accessHireDate));
            }
            $this->commit();
        } catch (\Exception $e) {
            $this->getLogger()->error('batchCreateAccessHireDate:'.$e->getMessage(), $accessHireDates);
            $this->rollback();
            throw $e;
        }
    }

    public function findAccessScopesByResourceTypeAndResourceId($resourceType, $resourceId)
    {
        $scopes = $this->getResourceAccessScopeHireDateDao()->findByResourceTypeAndResourceId($resourceType, $resourceId);

        return $scopes;
    }

    public function findVisibleResourceIds($resourceType, $userId, $preResultIds = array())
    {
        return array();
    }

    public function findVisibleScopesByResourceTypeAndResourceId($resourceType, $resourceId)
    {
        return array();
    }

    protected function isSatisfyHireDateRange($hireDateAccessScope, $userHireDate)
    {
        $result = false;
        switch ($hireDateAccessScope['hireDateType']) {
            case 'between':
                if (strtotime($hireDateAccessScope['hireStartDate']) < $userHireDate && $userHireDate < strtotime($hireDateAccessScope['hireEndDate'])) {
                    $result = true;
                }
                break;
            case 'before':
                if (strtotime($hireDateAccessScope['date']) > $userHireDate) {
                    $result = true;
                }
                break;
            case 'after':
                if (strtotime($hireDateAccessScope['date']) < $userHireDate) {
                    $result = true;
                }
                break;
            case 'greatThanOrEqual':
                if ($hireDateAccessScope['days'] * 86400 <= time() - $userHireDate) {
                    $result = true;
                }
                break;
            case 'lessThanOrEqual':
                if ($hireDateAccessScope['days'] * 86400 > time() - $userHireDate) {
                    $result = true;
                }
                break;
            default:
                $result = true;
                break;
        }

        return $result;
    }

    /**
     * @return ResourceAccessScopeHireDateDao
     */
    protected function getResourceAccessScopeHireDateDao()
    {
        return $this->createDao('ResourceScope:ResourceAccessScopeHireDateDao');
    }
}
