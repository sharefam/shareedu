<?php

namespace CorporateTrainingBundle\Controller\ProjectPlan\Item;

use Symfony\Component\HttpFoundation\Request;

class OfflineExamItemController extends BaseItemController
{
    public function createAction(Request $request, $projectPlanId)
    {
        $this->hasManageRole();

        return $this->render(
            'project-plan/item/offline-exam.html.twig',
            array(
                'projectPlanId' => $projectPlanId,
            )
        );
    }

    public function updateAction(Request $request, $projectPlanId, $offlineExamId)
    {
        $this->hasManageRole();
        $item = $this->getProjectPlanService()->getProjectPlanItemByProjectPlanIdAndTargetIdAndTargetType($projectPlanId, $offlineExamId, 'offline_exam');
        $offlineExam = $this->getOfflineExamService()->getOfflineExam($offlineExamId);
        $offlineExam['score'] = (int) $offlineExam['score'];
        $offlineExam['passScore'] = (int) $offlineExam['passScore'];

        return $this->render(
            'project-plan/item/offline-exam.html.twig',
            array(
                'projectPlanId' => $projectPlanId,
                'offlineExam' => $offlineExam,
                'item' => $item,
            )
        );
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineExam\Service\Impl\OfflineExamServiceImpl
     */
    protected function getOfflineExamService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineExam:OfflineExamService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineExam\Service\Impl\MemberServiceImpl
     */
    protected function getOfflineExamMemberService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineExam:MemberService');
    }
}
