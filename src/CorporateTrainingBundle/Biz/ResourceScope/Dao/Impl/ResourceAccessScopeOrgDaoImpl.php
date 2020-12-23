<?php

namespace CorporateTrainingBundle\Biz\ResourceScope\Dao\Impl;

use CorporateTrainingBundle\Biz\ResourceScope\Dao\ResourceAccessScopeOrgDao;

class ResourceAccessScopeOrgDaoImpl extends AbstractResourceScopeDaoImpl implements ResourceAccessScopeOrgDao
{
    protected $table = 'resource_access_scope_org';
}
