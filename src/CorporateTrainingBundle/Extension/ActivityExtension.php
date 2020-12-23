<?php

namespace CorporateTrainingBundle\Extension;

use CorporateTrainingBundle\Biz\Activity\Type\Questionnaire;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use CorporateTrainingBundle\Biz\Activity\Type\OfflineCourse;
use AppBundle\Extension\ActivityExtension as BaseActivityExtension;

class ActivityExtension extends BaseActivityExtension implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $container)
    {
        parent::register($container);
        $container['activity_type.questionnaire'] = function ($biz) {
            return new Questionnaire($biz);
        };

        $container['activity_type.offlineCourseQuestionnaire'] = function ($biz) {
            return new Questionnaire($biz);
        };

        $container['activity_type.offlineCourse'] = function ($biz) {
            return new OfflineCourse($biz);
        };
    }

    public function getActivities()
    {
        $activities = parent::getActivities();
        $biz = $this->biz;
        $survey = array('questionnaire' => array(
            'meta' => array(
                'name' => 'survey.activity.evaluation_survey',
                'icon' => 'es-icon es-icon-questionnaire_1',
            ),
            'controller' => 'SurveyPlugin:Activity/Questionnaire',
            'visible' => function ($courseSet, $course) use ($biz) {
                return true;
            },
        ));

        return array_merge($activities, $survey);
    }

    public function getTrainingActivities()
    {
        return array(
            'offlineCourse' => array(
                'controller' => 'CorporateTrainingBundle:OfflineCourse/Task/OfflineCourseTask',
            ),
            'questionnaire' => array(
                'controller' => 'CorporateTrainingBundle:OfflineCourse/Task/QuestionnaireTask',
            ),
        );
    }
}
