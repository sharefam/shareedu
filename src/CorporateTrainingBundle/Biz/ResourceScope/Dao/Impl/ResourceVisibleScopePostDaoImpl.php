<?php

namespace CorporateTrainingBundle\Biz\ResourceScope\Dao\Impl;

use CorporateTrainingBundle\Biz\ResourceScope\Dao\ResourceVisibleScopePostDao;

class ResourceVisibleScopePostDaoImpl extends AbstractResourceScopeDaoImpl implements ResourceVisibleScopePostDao
{
    protected $table = 'resource_visible_scope_post';
}
