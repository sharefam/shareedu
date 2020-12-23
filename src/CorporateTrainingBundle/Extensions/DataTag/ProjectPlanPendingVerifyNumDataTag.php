<?php

namespace CorporateTrainingBundle\Extensions\DataTag;

use AppBundle\Extensions\DataTag\DataTag;
use AppBundle\Extensions\DataTag\BaseDataTag;

class ProjectPlanPendingVerifyNumDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取培训项目待审核数量.
     *
     * @param array $arguments 参数
     *
     * @return int 报名记录数量
     */
    public function getData(array $arguments)
    {
        $projectPlan = $this->getProjectPlanService()->getProjectPlan($arguments['projectPlanId']);
        if (empty($projectPlan)) {
            throw new \InvalidArgumentException($this->getServiceKernel()->trans('培训项目不存在'));
        }
        $pendingVerifyNum = $this->getProjectPlanMemberService()->countEnrollmentRecords(array('projectPlanId' => $arguments['projectPlanId'], 'status' => 'submitted'));

        return $pendingVerifyNum;
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\Impl\ProjectPlanServiceImpl
     */
    protected function getProjectPlanService()
    {
        return $this->getServiceKernel()->getBiz()->service('CorporateTrainingBundle:ProjectPlan:ProjectPlanService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\Impl\MemberServiceImpl
     */
    protected function getProjectPlanMemberService()
    {
        return $this->getServiceKernel()->getBiz()->service('CorporateTrainingBundle:ProjectPlan:MemberService');
    }
}
