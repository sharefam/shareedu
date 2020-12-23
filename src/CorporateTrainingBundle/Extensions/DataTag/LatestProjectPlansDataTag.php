<?php

namespace CorporateTrainingBundle\Extensions\DataTag;

use AppBundle\Extensions\DataTag\DataTag;
use AppBundle\Extensions\DataTag\BaseDataTag;

class LatestProjectPlansDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取最新培训项目列表.
     *
     * 可传入的参数：
     *   count    必需 课程数量，取值不能超过100
     *
     * @param array $arguments 参数
     *
     * @return array 培训项目
     */
    public function getData(array $arguments)
    {
        $this->checkCount($arguments);
        $conditions = array('requireEnrollment' => 1, 'status' => 'published');
        if (!empty($arguments['categoryId'])) {
            $conditions['categoryId'] = $arguments['categoryId'];
        }

        $projectPlans = $this->getProjectPlanService()->searchProjectPlans(
            $conditions,
            array('createdTime' => 'DESC'),
            0,
            $arguments['count']
        );

        foreach ($projectPlans as &$projectPlan) {
            $projectPlan['enrolledNum'] = $this->getProjectPlanMemberService()->countProjectPlanMembers(array('projectPlanId' => $projectPlan['id']));
        }

        return $projectPlans;
    }

    public function checkCount($arguments)
    {
        if (empty($arguments['count'])) {
            throw new \InvalidArgumentException($this->getServiceKernel()->trans('count参数缺失'));
        }

        if ($arguments['count'] > 100) {
            throw new \InvalidArgumentException($this->getServiceKernel()->trans('count参数超出最大取值范围'));
        }
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\Impl\ProjectPlanServiceImpl
     */
    protected function getProjectPlanService()
    {
        return $this->getServiceKernel()->createService('CorporateTrainingBundle:ProjectPlan:ProjectPlanService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\Impl\MemberServiceImpl
     */
    protected function getProjectPlanMemberService()
    {
        return $this->getServiceKernel()->createService('CorporateTrainingBundle:ProjectPlan:MemberService');
    }
}
