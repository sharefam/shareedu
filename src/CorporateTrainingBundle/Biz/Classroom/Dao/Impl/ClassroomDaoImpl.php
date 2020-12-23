<?php

namespace CorporateTrainingBundle\Biz\Classroom\Dao\Impl;

use Biz\Classroom\Dao\Impl\ClassroomDaoImpl as BaseClassroomDao;

class ClassroomDaoImpl extends BaseClassroomDao
{
    public function initOrgsRelation($fields)
    {
        $sql = "UPDATE {$this->table} SET orgId = ?, orgCode = ?";

        return $this->db()->executeUpdate($sql, $fields);
    }

    public function declares()
    {
        $declares = parent::declares();

        array_push($declares['conditions'], 'orgId IN (:orgIds)');
        array_push($declares['conditions'], 'orgCode IN ( :orgCodes )');

        return $declares;
    }
}
