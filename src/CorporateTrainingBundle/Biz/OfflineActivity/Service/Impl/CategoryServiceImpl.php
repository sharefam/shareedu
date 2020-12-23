<?php

namespace CorporateTrainingBundle\Biz\OfflineActivity\Service\Impl;

use CorporateTrainingBundle\Biz\OfflineActivity\Service\CategoryService;
use Biz\Taxonomy\Service\Impl\CategoryServiceImpl as BaseCategoryServiceImpl;

class CategoryServiceImpl extends BaseCategoryServiceImpl implements CategoryService
{
    public function countCategories($conditions)
    {
        return $this->getCategoryDao()->count($conditions);
    }

    public function searchCategories($conditions, $orderBy, $start, $limit)
    {
        return $this->getCategoryDao()->search($conditions, $orderBy, $start, $limit);
    }

    protected function getCategoryDao()
    {
        return $this->createDao('CorporateTrainingBundle:OfflineActivity:CategoryDao');
    }
}
