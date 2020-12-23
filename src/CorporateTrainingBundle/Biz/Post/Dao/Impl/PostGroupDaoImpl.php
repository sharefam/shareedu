<?php

namespace CorporateTrainingBundle\Biz\Post\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use CorporateTrainingBundle\Biz\Post\Dao\PostGroupDao;

class PostGroupDaoImpl extends GeneralDaoImpl implements PostGroupDao
{
    protected $table = 'post_group';

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
            'serializes' => array(
                'rankGroupIds' => 'delimiter',
            ),
            'orderbys' => array('id', 'seq'),
            'conditions' => array(
                'name = :name',
                'visible = :visible',
                'id NOT IN ( :excludeIds )',
                'id IN ( :ids )',
            ),
        );
    }
}
