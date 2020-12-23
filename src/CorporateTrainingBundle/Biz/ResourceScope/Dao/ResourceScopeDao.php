<?php

namespace CorporateTrainingBundle\Biz\ResourceScope\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ResourceScopeDao extends GeneralDaoInterface
{
    public function findResourceIdsByResourceTypeAndScopes($resourceType, $scopes);

    public function findByResourceTypeAndResourceId($resourceType, $resourceId);

    public function countByResourceTypeAndResourceId($resourceType, $resourceId);

    public function findResourceIdsByResourceTypeAndScopesAndResourceIds($resourceType, $scopes, $resourceIds);

    public function findResourceIdsByResourceTypeAndResourceIds($resourceType, $resourceIds);
}
