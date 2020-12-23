<?php

namespace CorporateTrainingBundle\Biz\TeacherProfessionField\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use CorporateTrainingBundle\Biz\TeacherProfessionField\Dao\TeacherProfessionFieldDao;

class TeacherProfessionFieldDaoImpl extends GeneralDaoImpl implements TeacherProfessionFieldDao
{
    protected $table = 'teacher_profession_field';

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
            'orderbys' => array('id', 'seq'),
            'conditions' => array(
                'id = :id',
                'name = :name',
                'seq > :seq_GT',
                'id NOT IN ( :excludeIds )',
                'id IN (:ids)',
                'name LIKE :likeName',
            ),
        );
    }
}
