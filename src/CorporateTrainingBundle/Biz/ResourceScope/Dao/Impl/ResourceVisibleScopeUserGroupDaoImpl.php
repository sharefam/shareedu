<?php

namespace CorporateTrainingBundle\Biz\ResourceScope\Dao\Impl;

use CorporateTrainingBundle\Biz\ResourceScope\Dao\ResourceVisibleScopeUserGroupDao;

class ResourceVisibleScopeUserGroupDaoImpl extends AbstractResourceScopeDaoImpl implements ResourceVisibleScopeUserGroupDao
{
    protected $table = 'resource_visible_scope_user_group';
}
