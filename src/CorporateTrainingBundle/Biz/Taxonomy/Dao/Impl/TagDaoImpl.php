<?php

namespace CorporateTrainingBundle\Biz\Taxonomy\Dao\Impl;

use Biz\Taxonomy\Dao\Impl\TagDaoImpl as BaseTagDao;

class TagDaoImpl extends BaseTagDao
{
    public function initOrgsRelation($fields)
    {
        $sql = "UPDATE {$this->table} SET orgId = ?, orgCode = ?";

        return $this->db()->executeUpdate($sql, $fields);
    }
}
