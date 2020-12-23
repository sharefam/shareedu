<?php

namespace CorporateTrainingBundle\Biz\DefaultSearch;

use Biz\BaseService;

abstract class AbstractSearch extends BaseService
{
    abstract public function search($request, $keywords);

    abstract public function count($request, $keywords);

    protected function getResourceVisibleScopeService()
    {
        return $this->createService('ResourceScope:ResourceVisibleScopeService');
    }
}
