<?php

namespace CorporateTrainingBundle\Biz\DiscoveryColumn;

use Biz\DiscoveryColumn\Service\DiscoveryColumnService;
use CorporateTrainingBundle\Biz\ResourceScope\Service\ResourceVisibleScopeService;
use CorporateTrainingBundle\Biz\Taxonomy\Service\CategoryService;

abstract class BaseColumn extends AbstractColumn
{
    protected function buildColumnsCategoryName($columns)
    {
        foreach ($columns as $key => $column) {
            if (!empty($column['categoryId'])) {
                $category = $this->getCategoryService()->getCategory($column['categoryId']);
                $columns[$key]['categoryName'] = $category['name'];
            }
        }

        return $columns;
    }

    protected function getCurrentUser()
    {
        return $this->biz['user'];
    }

    /**
     * @return DiscoveryColumnService
     */
    protected function getDiscoveryColumnService()
    {
        return $this->createService('DiscoveryColumn:DiscoveryColumnService');
    }

    /**
     * @return CategoryService
     */
    protected function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }

    /**
     * @return ResourceVisibleScopeService
     */
    protected function getResourceVisibleScopeService()
    {
        return $this->createService('ResourceScope:ResourceVisibleScopeService');
    }

    protected function createService($alias)
    {
        return $this->biz->service($alias);
    }
}
