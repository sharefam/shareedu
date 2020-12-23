<?php

namespace CorporateTrainingBundle\Biz\Activity\Type;

use Biz\Activity\Config\Activity;
use Biz\Activity\Service\ActivityService;

class Questionnaire extends Activity
{
    protected function registerListeners()
    {
        return array();
    }

    public function create($fields)
    {
        $biz = $this->getBiz();
        $user = $biz['user'];
        $survey = array(
            'name' => $fields['title'],
            'type' => $fields['type'],
            'status' => 'published',
            'questionnaireId' => $fields['mediaId'],
            'createdUserId' => $user['id'],
        );

        return $this->getSurveyDao()->create($survey);
    }

    public function copy($activity, $config = array())
    {
        $biz = $this->getBiz();
        $user = $biz['user'];
        $survey = $this->getSurveyService()->getSurvey($activity['mediaId']);
        $copySurvey = array(
            'name' => $survey['name'],
            'type' => 'course',
            'status' => 'published',
            'questionnaireId' => $survey['questionnaireId'],
            'createdUserId' => $user['id'],
        );

        return $this->getSurveyDao()->create($copySurvey);
    }

    public function sync($sourceActivity, $activity)
    {
        $sourceSurvey = $this->getSurveyService()->getSurvey($sourceActivity['mediaId']);
        $survey = $this->getSurveyService()->getSurvey($activity['mediaId']);
        $survey['name'] = $sourceSurvey['name'];
        $survey['questionnaireId'] = $sourceSurvey['questionnaireId'];

        return $this->getSurveyDao()->update($survey['id'], $survey);
    }

    public function update($targetId, &$fields, $activity)
    {
        $survey = array(
            'name' => $fields['title'],
            'type' => 'course',
            'status' => 'published',
            'questionnaireId' => $fields['mediaId'],
        );

        return $this->getSurveyDao()->update($activity['mediaId'], $survey);
    }

    public function delete($targetId)
    {
        return  $this->getSurveyDao()->delete($targetId);
    }

    public function isFinished($activityId)
    {
        $biz = $this->getBiz();
        $user = $biz['user'];

        $activity = $this->getActivityService()->getActivity($activityId);
        $survey = $this->getSurveyService()->getSurvey($activity['mediaId']);
        $surveyResult = $this->getSurveyResultService()->getSurveyResultByUserIdAndSurveyIdAndStatus($user['id'], $survey['id'], 'finished');
        if (!empty($surveyResult)) {
            return true;
        }

        return false;
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }

    protected function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }

    protected function getSurveyService()
    {
        return $this->getBiz()->service('SurveyPlugin:Survey:SurveyService');
    }

    protected function getSurveyResultService()
    {
        return $this->getBiz()->service('SurveyPlugin:Survey:SurveyResultService');
    }

    protected function getSurveyDao()
    {
        return $this->getBiz()->dao('SurveyPlugin:Survey:SurveyDao');
    }
}
