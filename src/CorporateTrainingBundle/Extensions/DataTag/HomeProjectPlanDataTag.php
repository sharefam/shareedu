<?php

namespace CorporateTrainingBundle\Extensions\DataTag;

use AppBundle\Extensions\DataTag\DataTag;
use AppBundle\Extensions\DataTag\BaseDataTag;

class HomeProjectPlanDataTag extends BaseDataTag implements DataTag
{
    /**
     * 首页获取培训项目.
     *
     * @param array $arguments 参数
     *
     * @return array 培训项目
     */
    public function getData(array $arguments)
    {
        $conditions = array('requireEnrollment' => 1, 'status' => 'published', 'enrollmentEndDate_GE' => time());
        if (!empty($arguments['categoryId'])) {
            $conditions['categoryId'] = $arguments['categoryId'];
        }

        $conditions['ids'] = $this->getResourceVisibleScopeService()->findVisibleResourceIdsByResourceTypeAndUserId('projectPlan', $this->getCurrentUser()->getId());

        if (empty($arguments['count'])) {
            $arguments['count'] = PHP_INT_MAX;
        }

        $projectPlans = $this->getProjectPlanService()->searchProjectPlans($conditions, array('startTime' => 'DESC'), 0, $arguments['count']);

        if (count($projectPlans) > $arguments['count']) {
            $projectPlans = array_slice($projectPlans, 0, $arguments['count']);
        }

        return $projectPlans;
    }

    protected function getResourceVisibleScopeService()
    {
        return $this->getServiceKernel()->getBiz()->service('ResourceScope:ResourceVisibleScopeService');
    }

    protected function getProjectPlanService()
    {
        return $this->getServiceKernel()->getBiz()->service('CorporateTrainingBundle:ProjectPlan:ProjectPlanService');
    }
}
