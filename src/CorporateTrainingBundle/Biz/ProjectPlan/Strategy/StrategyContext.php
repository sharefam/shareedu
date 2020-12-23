<?php

namespace CorporateTrainingBundle\Biz\ProjectPlan\Strategy;

use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;
use CorporateTrainingBundle\Biz\ProjectPlan\Service\ProjectPlanService;

class StrategyContext
{
    protected $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    public function createStrategy($type)
    {
        $strategyType = $this->getStrategyType($type);

        if (!isset($this->biz[$strategyType])) {
            throw new NotFoundException("projectPlan strategy {$strategyType} does not exist");
        }

        return $this->biz[$strategyType];
    }

    protected function getStrategyType($type)
    {
        return $type.'_projectPlan_item';
    }

    /**
     * @return ProjectPlanService
     */
    protected function getProjectPlanService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:ProjectPlanService');
    }

    protected function createService($alias)
    {
        return $this->biz->service($alias);
    }
}
