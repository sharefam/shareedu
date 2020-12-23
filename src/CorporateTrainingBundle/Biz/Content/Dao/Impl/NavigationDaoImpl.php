<?php

namespace CorporateTrainingBundle\Biz\Content\Dao\Impl;

use Biz\Content\Dao\Impl\NavigationDaoImpl as BaseNavigationDao;

class NavigationDaoImpl extends BaseNavigationDao
{
    public function initOrgsRelation($fields)
    {
        $sql = "UPDATE {$this->table} SET orgId = ?, orgCode = ?";

        return $this->db()->executeUpdate($sql, $fields);
    }
}
