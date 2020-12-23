<?php

namespace CorporateTrainingBundle\Biz\Mail;

use AppBundle\Extension\Extension;
use CorporateTrainingBundle\Biz\Mail\Template\LiveCourseTemplate;
use CorporateTrainingBundle\Biz\Mail\Template\OfflineActivityAddMemberTemplate;
use CorporateTrainingBundle\Biz\Mail\Template\OfflineActivityApproveRejectTemplate;
use CorporateTrainingBundle\Biz\Mail\Template\OfflineCourseQuestionnaireTemplate;
use CorporateTrainingBundle\Biz\Mail\Template\OfflineCourseTemplate;
use CorporateTrainingBundle\Biz\Mail\Template\OfflineExamStartTemplate;
use CorporateTrainingBundle\Biz\Mail\Template\PostAddCourseTemplate;
use CorporateTrainingBundle\Biz\Mail\Template\ProjectPlanApproveRejectTemplate;
use CorporateTrainingBundle\Biz\Mail\Template\ProjectPlanAssignTemplate;
use CorporateTrainingBundle\Biz\Mail\Template\ProjectPlanEnrollResultTemplate;
use CorporateTrainingBundle\Biz\Mail\Template\ProjectPlanUpdateTemplate;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class MailServiceProvider extends Extension implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $biz)
    {
        $this->registerEmailTemplate($biz);

        $biz['notification_mail'] = function () {
            return function ($mailOptions) {
                return new NotificationMail($mailOptions);
            };
        };

        $biz['notification_cloud_mail'] = function () {
            return function ($mailOptions) {
                return new NotificationCloudMail($mailOptions);
            };
        };

        $biz['ct_mail_factory'] = $biz->factory(function ($biz) {
            $cloudConfig = $biz->service('System:SettingService')->get('cloud_email_crm', array());

            return function ($mailOptions) use ($cloudConfig, $biz) {
                if (isset($cloudConfig['status']) && 'enable' == $cloudConfig['status']) {
                    $mail = $biz['notification_cloud_mail']($mailOptions);
                } else {
                    $mail = $biz['notification_mail']($mailOptions);
                }

                $mail->setBiz($biz);

                return $mail;
            };
        });
    }

    private function registerEmailTemplate(Container $biz)
    {
        $biz['ct_email_template_paths'] = $biz->factory(function () {
            return array();
        });

        $biz['ct_email_template_paths'] = $biz->extend('ct_email_template_paths', function ($paths, $biz) {
            return array_merge($paths, array(__DIR__.'/Template/twig'));
        });

        /*
         * @Custom
         * 内训邮件通知模板注入
         */
        $biz['email_project_plan_enroll_result_template'] = function ($biz) {
            $template = new ProjectPlanEnrollResultTemplate();
            $template->setBiz($biz);

            return $template;
        };

        /*
         * @Custom
         * 内训邮件通知模板注入
         */
        $biz['email_project_plan_enroll_reject_template'] = function ($biz) {
            $template = new ProjectPlanApproveRejectTemplate();
            $template->setBiz($biz);

            return $template;
        };

        /*
         * @Custom
         * 内训邮件通知模板注入
         */
        $biz['post_course_add_template'] = function ($biz) {
            $template = new PostAddCourseTemplate();
            $template->setBiz($biz);

            return $template;
        };

        /*
         * @Custom
         * 内训邮件通知模板注入
         */
        $biz['offline_course_task_template'] = function ($biz) {
            $template = new OfflineCourseTemplate();
            $template->setBiz($biz);

            return $template;
        };

        /*
         * @Custom
         * 内训邮件通知模板注入
         */
        $biz['project_plan_assign_template'] = function ($biz) {
            $template = new ProjectPlanAssignTemplate();
            $template->setBiz($biz);

            return $template;
        };

        /*
         * @Custom
         * 内训邮件通知模板注入
         */
        $biz['project_plan_update_template'] = function ($biz) {
            $template = new ProjectPlanUpdateTemplate();
            $template->setBiz($biz);

            return $template;
        };

        /*
         * @Custom
         * 内训邮件通知模板注入
         */
        $biz['project_plan_create_offline_exam_template'] = function ($biz) {
            $template = new OfflineExamStartTemplate();
            $template->setBiz($biz);

            return $template;
        };

        /*
         * @Custom
         * 内训邮件通知模板注入
         */
        $biz['live_course_start_template'] = function ($biz) {
            $template = new LiveCourseTemplate();
            $template->setBiz($biz);

            return $template;
        };

        /*
         * @Custom
         * 内训邮件通知模板注入
         */
        $biz['offline_activity_add_member_template'] = function ($biz) {
            $template = new OfflineActivityAddMemberTemplate();
            $template->setBiz($biz);

            return $template;
        };

        /*
         * @Custom
         * 内训邮件通知模板注入
         */
        $biz['offline_activity_approve_reject_template'] = function ($biz) {
            $template = new OfflineActivityApproveRejectTemplate();
            $template->setBiz($biz);

            return $template;
        };

        /*
         * @Custom
         * 内训邮件通知模板注入
         */
        $biz['offline_course_questionnaire_template'] = function ($biz) {
            $template = new OfflineCourseQuestionnaireTemplate();
            $template->setBiz($biz);

            return $template;
        };
    }
}
