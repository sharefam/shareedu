<?php

namespace CorporateTrainingBundle\Biz\Taxonomy\Service;

use Biz\Taxonomy\Service\CategoryService as BaseService;

interface CategoryService extends BaseService
{
    public function updateGroup($id, array $fields);
}
