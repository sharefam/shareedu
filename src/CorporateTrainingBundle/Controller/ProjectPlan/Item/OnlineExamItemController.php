<?php

namespace CorporateTrainingBundle\Controller\ProjectPlan\Item;

use Symfony\Component\HttpFoundation\Request;

class OnlineExamItemController extends BaseItemController
{
    public function createAction(Request $request, $projectPlanId)
    {
        $this->hasManageRole();

        return $this->render(
            'project-plan/item/online-exam.html.twig',
            array(
                'projectPlanId' => $projectPlanId,
            )
        );
    }

    public function updateAction(Request $request, $projectPlanId, $examId)
    {
        $this->hasManageRole();
        $item = $this->getProjectPlanService()->getProjectPlanItemByProjectPlanIdAndTargetIdAndTargetType($projectPlanId, $examId, 'exam');
        $exam = $this->getExamService()->getExam($examId);
        $exam['passScore'] = (int) $exam['passScore'];
        $testPaper = $this->getTestPaperService()->getTestPaper($exam['testPaperId']);
        $testPapers = array(
            'id' => $testPaper['id'],
            'name' => $testPaper['name'],
            'score' => $testPaper['score'],
        );

        return $this->render(
            'project-plan/item/online-exam.html.twig',
            array(
                'item' => $item,
                'projectPlanId' => $projectPlanId,
                'exam' => $exam,
                'testPapers' => $testPapers,
            )
        );
    }

    /**
     * @return TestPaperService
     */
    protected function getTestPaperService()
    {
        return $this->createService('ExamPlugin:TestPaper:TestPaperService');
    }

    /**
     * @return \ExamPlugin\Biz\Exam\Service\Impl\ExamServiceImpl
     */
    protected function getExamService()
    {
        return $this->createService('ExamPlugin:Exam:ExamService');
    }
}
