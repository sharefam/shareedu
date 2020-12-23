<?php

namespace CorporateTrainingBundle\Biz\OfflineActivity\Dao\Impl;

use Biz\Taxonomy\Dao\Impl\CategoryDaoImpl as BaseCategoryDaoImpl;

class CategoryDaoImpl extends BaseCategoryDaoImpl
{
    public function declares()
    {
        return array(
            'orderbys' => array('id', 'weight'),
            'conditions' => array(
                'name LIKE :name',
                'groupId = :groupId',
            ),
        );
    }
}
