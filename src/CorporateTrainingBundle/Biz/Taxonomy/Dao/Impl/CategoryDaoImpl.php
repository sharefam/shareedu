<?php

namespace CorporateTrainingBundle\Biz\Taxonomy\Dao\Impl;

use Biz\Taxonomy\Dao\Impl\CategoryDaoImpl as BaseCategoryDao;

class CategoryDaoImpl extends BaseCategoryDao
{
    public function initOrgsRelation($fields)
    {
        $sql = "UPDATE {$this->table} SET orgId = ?, orgCode = ?";

        return $this->db()->executeUpdate($sql, $fields);
    }

    public function findByGroupIdAndNames($groupId, $names)
    {
        $marks = str_repeat('?,', count($names) - 1).'?';
        $sql = "SELECT * FROM {$this->table()} WHERE groupId = ? AND name IN ({$marks})";

        return $this->db()->fetchAll($sql, array_merge(array($groupId), $names));
    }

    public function declares()
    {
        return array(
            'orderbys' => array('id', 'createdTime', 'updatedTime', 'weight'),
            'conditions' => array(
                'id = :id',
                'name LIKE :name',
                'groupId = :groupId',
                'parentId IN ( :parentIds )',
            ),
        );
    }
}
