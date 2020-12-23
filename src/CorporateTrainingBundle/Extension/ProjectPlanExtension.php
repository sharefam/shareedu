<?php

namespace CorporateTrainingBundle\Extension;

use Pimple\Container;
use CorporateTrainingBundle\Biz\ProjectPlan\Strategy\StrategyContext;
use CorporateTrainingBundle\Biz\ProjectPlan\Strategy\Impl\CourseProjectPlanItemStrategyImpl;
use CorporateTrainingBundle\Biz\ProjectPlan\Strategy\Impl\OfflineCourseProjectPlanItemStrategyImpl;
use CorporateTrainingBundle\Biz\ProjectPlan\Strategy\Impl\ExamProjectPlanItemStrategyImpl;
use CorporateTrainingBundle\Biz\ProjectPlan\Strategy\Impl\OfflineExamProjectPlanItemStrategyImpl;
use Pimple\ServiceProviderInterface;

class ProjectPlanExtension extends Extension implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['projectPlan_item_strategy_context'] = function ($biz) {
            return new StrategyContext($biz);
        };

        $container['course_projectPlan_item'] = function ($biz) {
            return new CourseProjectPlanItemStrategyImpl($biz);
        };

        $container['offline_course_projectPlan_item'] = function ($biz) {
            return new OfflineCourseProjectPlanItemStrategyImpl($biz);
        };

        $container['exam_projectPlan_item'] = function ($biz) {
            return new ExamProjectPlanItemStrategyImpl($biz);
        };

        $container['offline_exam_projectPlan_item'] = function ($biz) {
            return new OfflineExamProjectPlanItemStrategyImpl($biz);
        };
    }

    public function getProjectPlanItems()
    {
        return array(
            'course' => array(
                'meta' => array(
                    'name' => 'project_plan.online_course',
                    'icon' => 'es-icon es-icon-book',
                ),
                'template' => 'project-plan/item/online-course.html.twig',
                'controller' => 'CorporateTrainingBundle:ProjectPlan/Item/OnlineCourseItem',
            ),
            'offlineCourse' => array(
                'meta' => array(
                    'name' => 'project_plan.offline_course',
                    'icon' => 'es-icon es-icon-teacher',
                ),
                'template' => 'project-plan/item/offline-course.html.twig',
                'controller' => 'CorporateTrainingBundle:ProjectPlan/Item/OfflineCourseItem',
            ),
            'exam' => array(
                'meta' => array(
                    'name' => 'project_plan.online_exam',
                    'icon' => 'es-icon es-icon-Online_Exam',
                ),
                'template' => 'project-plan/item/online-course.html.twig',
                'controller' => 'CorporateTrainingBundle:ProjectPlan/Item/OnlineExamItem',
            ),
            'offlineExam' => array(
                'meta' => array(
                    'name' => 'project_plan.offline_exam',
                    'icon' => 'es-icon es-icon-Offline_Exam',
                ),
                'template' => 'project-plan/item/offline-course.html.twig',
                'controller' => 'CorporateTrainingBundle:ProjectPlan/Item/OfflineExamItem',
            ),
        );
    }
}
