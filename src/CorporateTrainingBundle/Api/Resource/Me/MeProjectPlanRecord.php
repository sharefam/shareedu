<?php

namespace CorporateTrainingBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use CorporateTrainingBundle\Biz\ProjectPlan\Service\MemberService;
use CorporateTrainingBundle\Biz\ProjectPlan\Service\ProjectPlanService;

class MeProjectPlanRecord extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $user = $this->getCurrentUser();
        $total = $this->getProjectPlanMemberService()->countProjectPlanMembers(array('userId' => $user['id']));

        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $projectPlanMembers = $this->getProjectPlanMemberService()->searchProjectPlanMembers(
            array('userId' => $user['id']),
            array('id' => 'DESC'),
            $offset,
            $limit
        );

        $projectPlanMembers = ArrayToolkit::index($projectPlanMembers, 'projectPlanId');
        $projectPlans = $this->getProjectPlanService()->findProjectPlansByIds(ArrayToolkit::column($projectPlanMembers, 'projectPlanId'));
        $projectPlans = ArrayToolkit::index($projectPlans, 'id');

        foreach ($projectPlanMembers as $key => $member) {
            $projectPlan = empty($projectPlans[$member['projectPlanId']]) ? array() : $projectPlans[$member['projectPlanId']];
            $projectPlanMembers[$key]['projectPlanName'] = empty($projectPlan) ? '' : $projectPlan['name'];
            $projectPlanMembers[$key]['startTime'] = empty($projectPlan) ? '' : $projectPlan['startTime'];
            $projectPlanMembers[$key]['endTime'] = empty($projectPlan) ? '' : $projectPlan['endTime'];
            $projectPlanMembers[$key]['progress'] = empty($projectPlan) ? 0 : round($this->getProjectPlanService()->getPersonalProjectPlanProgress($projectPlan['id'], $user['id']), 0);
        }

        $this->getOCUtil()->multiple($projectPlanMembers, array('userId'));

        return $this->makePagingObject(array_values($projectPlanMembers), $total, $offset, $limit);
    }

    /**
     * @return ProjectPlanService
     */
    protected function getProjectPlanService()
    {
        return $this->service('CorporateTrainingBundle:ProjectPlan:ProjectPlanService');
    }

    /**
     * @return MemberService
     */
    protected function getProjectPlanMemberService()
    {
        return $this->service('CorporateTrainingBundle:ProjectPlan:MemberService');
    }
}
