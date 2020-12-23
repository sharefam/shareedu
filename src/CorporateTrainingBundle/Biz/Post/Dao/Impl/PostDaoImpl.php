<?php

namespace CorporateTrainingBundle\Biz\Post\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use CorporateTrainingBundle\Biz\Post\Dao\PostDao;

class PostDaoImpl extends GeneralDaoImpl implements PostDao
{
    protected $table = 'post';

    public function getByName($name)
    {
        return $this->getByFields(array('name' => $name));
    }

    public function getByCode($code)
    {
        return $this->getByFields(array('code' => $code));
    }

    public function getByGroupIdAndRankId($groupId, $rankId)
    {
        return $this->getByFields(array('groupId' => $groupId, 'rankId' => $rankId));
    }

    public function findAll()
    {
        return $this->search(array(), array('seq' => 'ASC', 'id' => 'ASC'), 0, PHP_INT_MAX);
    }

    public function findByGroupId($groupId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE groupId = ? ORDER BY seq ASC";

        return $this->db()->fetchAll($sql, array($groupId)) ?: array();
    }

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function findBySeqAndGroupId($seq, $groupId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE seq > ? AND groupId = ?";

        return $this->db()->fetchAll($sql, array($seq, $groupId)) ?: array();
    }

    public function getPostByCode($code)
    {
        return $this->getByFields(array('code' => $code));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'serializes' => array(),
            'orderbys' => array('id', 'seq'),
            'conditions' => array(
                'id NOT IN (:excludeIds)',
                'rankId IN (:rankIds)',
                'id IN (:ids)',
                'name LIKE :likeName',
                'name = :name',
                'code = :code',
                'groupId = :groupId',
                'createdUserId = :createdUserId',
                'courseId = :courseId',
                'rankId = :rankId',
            ),
        );
    }
}
