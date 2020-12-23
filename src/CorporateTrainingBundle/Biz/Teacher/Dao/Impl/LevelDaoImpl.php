<?php

namespace CorporateTrainingBundle\Biz\Teacher\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use CorporateTrainingBundle\Biz\Teacher\Dao\LevelDao;

class LevelDaoImpl extends GeneralDaoImpl implements LevelDao
{
    protected $table = 'teacher_level';

    public function getByName($name)
    {
        return $this->getByFields(array('name' => $name));
    }

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'serializes' => array(),
            'orderbys' => array('id', 'createdTime'),
            'conditions' => array(
                'id IN (:ids)',
                'id NOT IN (:excludeIds)',
                'name LIKE :likeName',
                'name = :name',
                'id = :id',
                'createdUserId = :createdUserId',
            ),
        );
    }
}
