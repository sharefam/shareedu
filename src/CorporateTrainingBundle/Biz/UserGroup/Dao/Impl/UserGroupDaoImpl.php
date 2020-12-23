<?php

namespace CorporateTrainingBundle\Biz\UserGroup\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use CorporateTrainingBundle\Biz\UserGroup\Dao\UserGroupDao;

class UserGroupDaoImpl extends GeneralDaoImpl implements UserGroupDao
{
    protected $table = 'user_group';

    public function getByName($name)
    {
        return $this->getByFields(array('name' => $name));
    }

    public function getByCode($code)
    {
        return $this->getByFields(array('code' => $code));
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
            'orderbys' => array('id', 'createdTime', 'updatedTime'),
            'conditions' => array(
                'id IN ( :ids)',
                'id = :id',
                'name LIKE :likeName',
                'name =: name',
                'code =: code',
                'createdUserId =: createdUserId',
            ),
        );
    }
}
