<?php

namespace CorporateTrainingBundle\Biz\DiscoveryColumn\Service\Impl;

use Biz\DiscoveryColumn\Service\Impl\DiscoveryColumnServiceImpl as BaseService;

class DiscoveryColumnServiceImpl extends BaseService
{
    public function getDisplayData()
    {
        $columns = $this->getDiscoveryColumnDao()->findAllOrderBySeq();

        foreach ($columns as &$column) {
            $columnFactory = $this->biz->offsetGet('column_factory');
            $discoveryColumn = $columnFactory->create($column['type']);
            $column = $discoveryColumn->buildColumn($column);
        }

        return $columns;
    }
}
