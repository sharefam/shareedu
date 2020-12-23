<?php

namespace CorporateTrainingBundle\Biz\DiscoveryColumn;

use CorporateTrainingBundle\Biz\ProjectPlan\Service\MemberService;
use CorporateTrainingBundle\Biz\ProjectPlan\Service\ProjectPlanService;

class ProjectPlanColumn extends BaseColumn
{
    public function buildColumn($column)
    {
        $condition = array(
            'status' => 'published',
            'enrollmentEndDate_GE' => time(),
            'requireEnrollment' => 1,
        );
        $condition['ids'] = $this->getResourceVisibleScopeService()->findVisibleResourceIdsByResourceTypeAndUserId('projectPlan', $this->getCurrentUser()->getId());

        $projectPlans = $this->getProjectPlanService()->searchProjectPlans(
            $condition,
            array('enrollmentEndDate' => 'ASC'),
            0,
            $column['showCount']
        );
        $projectPlans = $this->buildColumnsCategoryName($projectPlans);
        $column['data'] = $this->buildColumnsStudentNum($projectPlans);
        $column['actualCount'] = count($projectPlans);

        return $column;
    }

    protected function buildColumnsStudentNum($columns)
    {
        foreach ($columns as $key => &$column) {
            if (!empty($column['id'])) {
                $studentNum = $this->getProjectPlanMemberService()->countProjectPlanMembers(array('projectPlanId' => $column['id']));
                $column['title'] = $column['name'];
                $columns[$key]['studentNum'] = (string) $studentNum;
                unset($column['name']);
            }
        }

        return $columns;
    }

    /**
     * @return ProjectPlanService
     */
    protected function getProjectPlanService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:ProjectPlanService');
    }

    /**
     * @return MemberService
     */
    protected function getProjectPlanMemberService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:MemberService');
    }
}
