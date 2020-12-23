<?php

namespace CorporateTrainingBundle\Api\Resource\ProjectPlan;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use CorporateTrainingBundle\Biz\ProjectPlan\Service\ProjectPlanService;

class ProjectPlanItem extends AbstractResource
{
    public function search(ApiRequest $request, $projectPlanId)
    {
        $targetId = $request->query->get('targetId');
        $targetType = $request->query->get('targetType');

        $item = $this->getProjectPlanService()->getProjectPlanItemByProjectPlanIdAndTargetIdAndTargetType($projectPlanId, $targetId, $targetType);
        $itemDetail = $this->getItem($item);

        return $itemDetail;
    }

    protected function getItem($item)
    {
        $strategy = $this->createProjectPlanStrategy($item['targetType']);
        $item = $strategy->getItem($item);

        return isset($item['tasks']) ? $item['tasks'] : $item['detail'];
    }

    /**
     * @return ProjectPlanService
     */
    protected function getProjectPlanService()
    {
        return $this->service('CorporateTrainingBundle:ProjectPlan:ProjectPlanService');
    }

    protected function createProjectPlanStrategy($type)
    {
        return $this->biz->offsetGet('projectPlan_item_strategy_context')->createStrategy($type);
    }
}
