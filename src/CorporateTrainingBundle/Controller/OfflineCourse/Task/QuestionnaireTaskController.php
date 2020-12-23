<?php

namespace CorporateTrainingBundle\Controller\OfflineCourse\Task;

use Symfony\Component\HttpFoundation\Request;

class QuestionnaireTaskController extends BaseTaskController
{
    public function createAction(Request $request, $id, $type)
    {
        $offlineCourse = $this->getOfflineCourseService()->getOfflineCourse($id);

        return $this->render(
            'CorporateTrainingBundle::offline-course-manage/task/create-questionnaire-modal.html.twig',
            array(
                'course' => $offlineCourse,
                'type' => $type,
            )
        );
    }

    public function editAction(Request $request, $task, $activity)
    {
        $survey = $this->getSurveyService()->getSurvey($activity['mediaId']);
        $questionnaire = $this->getQuestionnaireService()->getQuestionnaire($survey['questionnaireId']);

        return $this->render(
            'CorporateTrainingBundle::offline-course-manage/task/edit-questionnaire-modal.html.twig',
            array(
                'questionnaire' => $questionnaire,
                'task' => $task,
                'activity' => $activity,
            )
        );
    }

    /**
     * @return \SurveyPlugin\Biz\Survey\Service\Impl\SurveyServiceImpl
     */
    protected function getSurveyService()
    {
        return $this->createService('SurveyPlugin:Survey:SurveyService');
    }

    /**
     * @return \SurveyPlugin\Biz\Questionnaire\Service\Impl\QuestionnaireServiceImpl
     */
    protected function getQuestionnaireService()
    {
        return $this->createService('SurveyPlugin:Questionnaire:QuestionnaireService');
    }
}
