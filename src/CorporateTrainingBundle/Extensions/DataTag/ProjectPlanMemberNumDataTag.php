<?php

namespace CorporateTrainingBundle\Extensions\DataTag;

use AppBundle\Extensions\DataTag\DataTag;
use AppBundle\Extensions\DataTag\BaseDataTag;

class ProjectPlanMemberNumDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取培训项目成员数量.
     *
     * @param array $arguments 参数
     *
     * @return int 成员数量
     */
    public function getData(array $arguments)
    {
        $group = $this->getProjectPlanService()->getProjectPlan($arguments['projectPlanId']);
        if (empty($group)) {
            throw new \InvalidArgumentException($this->getServiceKernel()->trans('培训项目不存在'));
        }
        $memberCounts = $this->getProjectPlanMemberService()->countProjectPlanMembers(array('projectPlanId' => $arguments['projectPlanId']));

        return $memberCounts;
    }

    protected function getProjectPlanService()
    {
        return $this->getServiceKernel()->getBiz()->service('CorporateTrainingBundle:ProjectPlan:ProjectPlanService');
    }

    protected function getProjectPlanMemberService()
    {
        return $this->getServiceKernel()->getBiz()->service('CorporateTrainingBundle:ProjectPlan:MemberService');
    }
}
