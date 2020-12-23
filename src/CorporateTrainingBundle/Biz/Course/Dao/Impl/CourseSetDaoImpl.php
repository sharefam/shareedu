<?php

namespace CorporateTrainingBundle\Biz\Course\Dao\Impl;

use Biz\Course\Dao\Impl\CourseSetDaoImpl as BaseDaoImpl;

class CourseSetDaoImpl extends BaseDaoImpl
{
    public function sumStudentNumByConditions($conditions)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select('sum(studentNum)');

        return $builder->execute()->fetchColumn(0);
    }

    public function findByCategoryId($categoryId)
    {
        return $this->findByFields(array('categoryId' => $categoryId));
    }

    public function declares()
    {
        $declares = parent::declares();
        array_push($declares['conditions'], 'orgCode = :orgCode');
        array_push($declares['conditions'], 'orgCode IN ( :orgCodes )');
        array_push($declares['conditions'], 'status IN ( :statuses )');
        array_push($declares['conditions'], 'title LIKE :titleLike');
        array_push($declares['conditions'], 'orgId IN ( :orgIds )');
        array_push($declares['conditions'], 'defaultCourseId IN ( :defaultCourseIds )');
        array_push($declares['conditions'], 'defaultCourseId  NOT IN ( :excludeDefaultCourseIds )');
        array_push($declares['conditions'], 'id NOT IN ( :excludeIds )');
        array_push($declares['conditions'], 'type IN ( :types)');
        array_push($declares['conditions'], 'orgId = :orgId');
        array_push($declares['conditions'], 'status NOT IN ( :excludeStatus )');

        return $declares;
    }

    public function initOrgsRelation($fields)
    {
        $sql = "UPDATE {$this->table} SET orgId = ?, orgCode = ?";

        return $this->db()->executeUpdate($sql, $fields);
    }
}
