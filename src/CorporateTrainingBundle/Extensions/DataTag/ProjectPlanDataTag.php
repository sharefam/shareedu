<?php

namespace CorporateTrainingBundle\Extensions\DataTag;

use AppBundle\Extensions\DataTag\DataTag;
use AppBundle\Extensions\DataTag\BaseDataTag;

class ProjectPlanDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取培训项目.
     *
     * @param array $arguments 参数
     *
     * @return array 培训项目
     */
    public function getData(array $arguments)
    {
        if (empty($arguments['projectPlanId'])) {
            throw new \InvalidArgumentException($this->getServiceKernel()->trans('projectPlanId参数缺失'));
        }
        $projectPlan = $this->getProjectPlanService()->getProjectPlan($arguments['projectPlanId']);

        return $projectPlan;
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\Impl\ProjectPlanServiceImpl
     */
    protected function getProjectPlanService()
    {
        return $this->getServiceKernel()->getBiz()->service('CorporateTrainingBundle:ProjectPlan:ProjectPlanService');
    }
}
