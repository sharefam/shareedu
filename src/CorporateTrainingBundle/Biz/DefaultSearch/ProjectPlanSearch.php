<?php

namespace CorporateTrainingBundle\Biz\DefaultSearch;

use AppBundle\Common\Paginator;

class ProjectPlanSearch extends AbstractSearch
{
    public function search($request, $keywords)
    {
        $conditions = $this->prepareSearchConditions($keywords);

        $courseSetNum = $this->getProjectPlanService()->countProjectPlans($conditions);

        $paginator = new Paginator(
            $request,
            $courseSetNum,
            10
        );

        $courseSets = $this->getProjectPlanService()->searchProjectPlans(
            $conditions,
            array('startTime' => 'desc'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return array($courseSets, $paginator);
    }

    public function count($request, $keywords)
    {
        $conditions = $this->prepareSearchConditions($keywords);
        $courseSetNum = $this->getProjectPlanService()->countProjectPlans($conditions);

        return $courseSetNum;
    }

    protected function prepareSearchConditions($keywords)
    {
        $user = $this->getCurrentUser();
        $scopeProjectPlanIds = $this->getResourceVisibleScopeService()->findVisibleResourceIdsByResourceTypeAndUserId('projectPlan',
            $user['id']);
        $conditions = array(
            'ids' => $scopeProjectPlanIds,
            'requireEnrollment' => '1',
            'status' => 'published',
            'nameLike' => $keywords,
        );

        return $conditions;
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\Impl\ProjectPlanServiceImpl
     */
    protected function getProjectPlanService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:ProjectPlanService');
    }
}
