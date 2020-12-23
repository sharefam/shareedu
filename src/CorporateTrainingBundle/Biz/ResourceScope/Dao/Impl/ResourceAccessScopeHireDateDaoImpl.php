<?php

namespace CorporateTrainingBundle\Biz\ResourceScope\Dao\Impl;

use CorporateTrainingBundle\Biz\ResourceScope\Dao\ResourceAccessScopeHireDateDao;

class ResourceAccessScopeHireDateDaoImpl extends AbstractResourceScopeDaoImpl implements ResourceAccessScopeHireDateDao
{
    protected $table = 'resource_access_scope_hire_date';

    public function declares()
    {
        $condition = parent::declares();
        $condition['serializes'] = array('scope' => 'json');

        return $condition;
    }
}
