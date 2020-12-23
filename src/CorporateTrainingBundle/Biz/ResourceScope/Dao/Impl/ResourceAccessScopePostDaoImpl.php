<?php

namespace CorporateTrainingBundle\Biz\ResourceScope\Dao\Impl;

use CorporateTrainingBundle\Biz\ResourceScope\Dao\ResourceAccessScopePostDao;

class ResourceAccessScopePostDaoImpl extends AbstractResourceScopeDaoImpl implements ResourceAccessScopePostDao
{
    protected $table = 'resource_access_scope_post';
}
