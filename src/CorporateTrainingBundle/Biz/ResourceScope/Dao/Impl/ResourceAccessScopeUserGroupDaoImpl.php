<?php

namespace CorporateTrainingBundle\Biz\ResourceScope\Dao\Impl;

use CorporateTrainingBundle\Biz\ResourceScope\Dao\ResourceAccessScopeUserGroupDao;

class ResourceAccessScopeUserGroupDaoImpl extends AbstractResourceScopeDaoImpl implements ResourceAccessScopeUserGroupDao
{
    protected $table = 'resource_access_scope_user_group';
}
