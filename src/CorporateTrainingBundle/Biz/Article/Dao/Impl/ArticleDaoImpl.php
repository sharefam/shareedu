<?php

namespace CorporateTrainingBundle\Biz\Article\Dao\Impl;

use Biz\Article\Dao\Impl\ArticleDaoImpl as BaseDaoImpl;

class ArticleDaoImpl extends BaseDaoImpl
{
    public function declares()
    {
        $declares = parent::declares();
        array_push($declares['conditions'], 'featured != :featuredNotEqual');

        return $declares;
    }

    public function initOrgsRelation($fields)
    {
        $sql = "UPDATE {$this->table} SET orgId = ?, orgCode = ?";

        return $this->db()->executeUpdate($sql, $fields);
    }
}
