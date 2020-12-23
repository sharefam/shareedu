<?php

namespace CorporateTrainingBundle\Biz\OfflineActivity\Service;

use Biz\Taxonomy\Service\CategoryService as BaseCategoryService;

interface CategoryService extends BaseCategoryService
{
    public function countCategories($conditions);

    public function searchCategories($conditions, $orderBy, $start, $limit);
}
