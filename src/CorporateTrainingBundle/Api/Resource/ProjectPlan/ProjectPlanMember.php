<?php

namespace CorporateTrainingBundle\Api\Resource\ProjectPlan;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\Resource\AbstractResource;
use CorporateTrainingBundle\Biz\ProjectPlan\Service\MemberService;
use CorporateTrainingBundle\Biz\ProjectPlan\Service\ProjectPlanService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProjectPlanMember extends AbstractResource
{
    public function add(ApiRequest $request, $projectPlanId)
    {
        $projectPlan = $this->getProjectPlanService()->getProjectPlan($projectPlanId);
        $userId = $this->getCurrentUser()->getId();
        if (empty($projectPlan)) {
            throw new NotFoundHttpException('培训项目不存在', null, ErrorCode::RESOURCE_NOT_FOUND);
        }

        if (empty($projectPlan['requireAudit'])) {
            $result = $this->getProjectPlanMemberService()->batchBecomeMember($projectPlanId, array($userId));
        } else {
            $canApplyProjectPlan = $this->getProjectPlanService()->canApplyAttendProjectPlan($projectPlanId, $userId);
            if ($canApplyProjectPlan) {
                $fields = array(
                    'userId' => $this->getCurrentUser()->getId(),
                    'projectPlanId' => $projectPlanId,
                    'status' => 'submitted',
                );
                $result = $this->getProjectPlanMemberService()->createEnrollmentRecord($fields);
            }
        }

        return array('result' => empty($result) ? false : true);
    }

    /**
     * @return MemberService
     */
    protected function getProjectPlanMemberService()
    {
        return $this->service('CorporateTrainingBundle:ProjectPlan:MemberService');
    }

    /**
     * @return ProjectPlanService
     */
    protected function getProjectPlanService()
    {
        return $this->service('CorporateTrainingBundle:ProjectPlan:ProjectPlanService');
    }
}
