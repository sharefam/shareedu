<?php

namespace CorporateTrainingBundle\Biz\DingTalk;

use AppBundle\Extension\Extension;
use CorporateTrainingBundle\Biz\DingTalk\Template\ClassroomCourseUpdateTemplate;
use CorporateTrainingBundle\Biz\DingTalk\Template\LiveCourseStartRemindTemplate;
use CorporateTrainingBundle\Biz\DingTalk\Template\OfflineActivityAddMemberTemplate;
use CorporateTrainingBundle\Biz\DingTalk\Template\OfflineActivityApproveRejectTemplate;
use CorporateTrainingBundle\Biz\DingTalk\Template\OfflineActivityRemindTemplate;
use CorporateTrainingBundle\Biz\DingTalk\Template\OfflineExamRemindTemplate;
use CorporateTrainingBundle\Biz\DingTalk\Template\OfflineExamResultTemplate;
use CorporateTrainingBundle\Biz\DingTalk\Template\ClassroomAssignTemplate;
use CorporateTrainingBundle\Biz\DingTalk\Template\OfflineActivityApplyTemplate;
use CorporateTrainingBundle\Biz\DingTalk\Template\OnlineCourseAssignTemplate;
use CorporateTrainingBundle\Biz\DingTalk\Template\OnlineCourseExamResultTemplate;
use CorporateTrainingBundle\Biz\DingTalk\Template\PostCourseAddTemplate;
use CorporateTrainingBundle\Biz\DingTalk\Template\ProjectPlanApproveRejectTemplate;
use CorporateTrainingBundle\Biz\DingTalk\Template\ProjectPlanAssignTemplate;
use CorporateTrainingBundle\Biz\DingTalk\Template\ProjectPlanEnrollResultTemplate;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class DingTalkServiceProvider extends Extension implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $biz)
    {
        $this->registerEmailTemplate($biz);
    }

    private function registerEmailTemplate(Container $biz)
    {
        $biz['dingtalk_template_parser'] = function ($biz) {
            $parser = new DingTalkTemplateParser();
            $parser->setBiz($biz);

            return $parser;
        };

        /*
         * @Custom
         * 内训钉钉通知模板注入
         * 岗位课程更新
         */
        $biz['dingtalk_template.post_course_add'] = function ($biz) {
            $template = new PostCourseAddTemplate();
            $template->setBiz($biz);

            return $template;
        };

        /*
         * @Custom
         * 内训钉钉通知模板注入
         * 培训项目指派成员
         */
        $biz['dingtalk_template.project_plan_assign'] = function ($biz) {
            $template = new ProjectPlanAssignTemplate();
            $template->setBiz($biz);

            return $template;
        };

        /*
         * @Custom
         * 内训钉钉通知模板注入
         * 线上课程指派成员
         */
        $biz['dingtalk_template.online_course_assign'] = function ($biz) {
            $template = new OnlineCourseAssignTemplate();
            $template->setBiz($biz);

            return $template;
        };

        /*
         * @Custom
         * 内训钉钉通知模板注入
         * 专题指派成员
         */
        $biz['dingtalk_template.classroom_assign'] = function ($biz) {
            $template = new ClassroomAssignTemplate();
            $template->setBiz($biz);

            return $template;
        };

        /*
         * @Custom
         * 内训钉钉通知模板注入
         * 专题添加课程
         */
        $biz['dingtalk_template.classroom_course_update'] = function ($biz) {
            $template = new ClassroomCourseUpdateTemplate();
            $template->setBiz($biz);

            return $template;
        };

        /*
         * @Custom
         * 内训钉钉通知模板注入
         * 线下活动报名
         */
        $biz['dingtalk_template.offline_activity_apply'] = function ($biz) {
            $template = new OfflineActivityApplyTemplate();
            $template->setBiz($biz);

            return $template;
        };

        /*
         * @Custom
         * 内训钉钉通知模板注入
         * 线下考试结果
         */
        $biz['dingtalk_template.offline_exam_result'] = function ($biz) {
            $template = new OfflineExamResultTemplate();

            $template->setBiz($biz);

            return $template;
        };

        /*
         * @Custom
         * 内训钉钉通知模板注入
         * 线下考试开考提醒
         */
        $biz['dingtalk_template.offline_exam_remind'] = function ($biz) {
            $template = new OfflineExamRemindTemplate();

            $template->setBiz($biz);

            return $template;
        };

        /*
         * @Custom
         * 内训钉钉通知模板注入
         * 培训项目报名
         */
        $biz['dingtalk_template.email_project_plan_enroll_result'] = function ($biz) {
            $template = new ProjectPlanEnrollResultTemplate();

            $template->setBiz($biz);

            return $template;
        };

        /*
         * @Custom
         * 内训钉钉通知模板注入
         * 培训项目审核不通过
         */
        $biz['dingtalk_template.email_project_plan_enroll_reject'] = function ($biz) {
            $template = new ProjectPlanApproveRejectTemplate();

            $template->setBiz($biz);

            return $template;
        };

        /*
         * @Custom
         * 内训钉钉通知模板注入
         * 线下活动指派成员
         */
        $biz['dingtalk_template.offline_activity_add_member'] = function ($biz) {
            $template = new OfflineActivityAddMemberTemplate();

            $template->setBiz($biz);

            return $template;
        };

        /*
         * @Custom
         * 内训钉钉通知模板注入
         * 线下活动审核不通过
         */
        $biz['dingtalk_template.offline_activity_approve_reject'] = function ($biz) {
            $template = new OfflineActivityApproveRejectTemplate();

            $template->setBiz($biz);

            return $template;
        };

        /*
         * @Custom
         * 内训钉钉通知模板注入
         * 线下活动开始提醒
         */
        $biz['dingtalk_template.offline_activity_remind'] = function ($biz) {
            $template = new OfflineActivityRemindTemplate();
            $template->setBiz($biz);

            return $template;
        };

        /*
         * @Custom
         * 内训钉钉通知模板注入
         * 直播开始提醒
         */
        $biz['dingtalk_template.live_course_start_remind'] = function ($biz) {
            $template = new LiveCourseStartRemindTemplate();
            $template->setBiz($biz);

            return $template;
        };

        /*
         * @Custom
         * 内训钉钉通知模板注入
         * 线上课程考试批阅
         */
        $biz['dingtalk_template.online_course_exam_result'] = function ($biz) {
            $template = new OnlineCourseExamResultTemplate();
            $template->setBiz($biz);

            return $template;
        };
    }
}
