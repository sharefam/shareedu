<?php

namespace CorporateTrainingBundle\Biz\Area\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use CorporateTrainingBundle\Biz\Area\Dao\AreaDao;

class AreaDaoImpl extends GeneralDaoImpl implements AreaDao
{
    protected $table = 'area';

    public function declares()
    {
        return array(
            'orderbys' => array(
                'parentId',
                'name',
                'id',
            ),
            'conditions' => array(
                'id IN ( :ids )',
                'parentId = :parentId',
                'name LIKE :name',
            ),
        );
    }

    public function findByParentId($parentId)
    {
        return $this->findByFields(array('parentId' => $parentId));
    }
}
