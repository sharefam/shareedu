<?php

namespace CorporateTrainingBundle\Biz\ResourceScope\Strategy;

use Codeages\Biz\Framework\Service\Exception\NotFoundException;

class ResourceScopeStrategyContext
{
    private $biz = null;

    private $visibleScopeTypes = array();

    private $accessScopeTypes = array();

    public function __construct($biz)
    {
        $this->biz = $biz;
    }

    protected function getStrategyType($type)
    {
        return 'resource_scope_strategy_' . $type;
    }

    public function createStrategy($type)
    {
        $strategyType = $this->getStrategyType($type);
        if (isset($this->biz[$strategyType])) {
            return $this->biz[$strategyType];
        }
        throw new NotFoundException("resource scope strategy {$strategyType} does not exist");
    }

    public function getVisibleScopeTypes()
    {
        return $this->visibleScopeTypes;
    }

    public function addVisibleScopeType($type)
    {
        $this->visibleScopeTypes[] = $type;
    }

    public function getAccessScopeTypes()
    {
        return $this->accessScopeTypes;
    }

    public function addAccessScopeType($type)
    {
        $this->accessScopeTypes[] = $type;
    }
}
