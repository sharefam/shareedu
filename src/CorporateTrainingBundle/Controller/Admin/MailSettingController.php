<?php

namespace CorporateTrainingBundle\Controller\Admin;

use AppBundle\Controller\Admin\BaseController;
use Biz\System\Service\SettingService;
use Symfony\Component\HttpFoundation\Request;

class MailSettingController extends BaseController
{
    public function setAction(Request $request)
    {
        $mailNotification = $this->getSettingService()->get('mail_notification', array());

        $default = array(
            'enroll' => 0,
            'post_assign' => 0,
            'project_plan_progress' => 0,
            'live_course' => 0,
            'project_plan_content' => 0,
            'my_task' => 0,
            'exam' => 0,
        );

        $mailNotification = array_merge($default, $mailNotification);

        if ('POST' === $request->getMethod()) {
            $mailNotification = $request->request->all();

            $this->getSettingService()->set('mail_notification', $mailNotification);
            $this->setFlashMessage('success', 'site.save.success');
        }

        return $this->render(
            'CorporateTrainingBundle::admin/mail/set.html.twig',
            array(
                'mailNotification' => $mailNotification,
            )
        );
    }

    public function previewTemplateAction(Request $request, $type)
    {
        return $this->render(
            'CorporateTrainingBundle::admin/mail/preview-modal.html.twig',
            $this->getTemplateByType($type)
        );
    }

    protected function getTemplateByType($type)
    {
        switch ($type) {
            case 'enroll':
                return $this->getEnrollTemplate();
            case 'post_assign':
                return $this->getPostAssignTemple();
            case 'project_plan_progress':
                return $this->getProjectPlanProgressTemple();
            case 'live_course':
                return $this->getLiveCourseTemple();
            case 'project_plan_content':
                return $this->getProjectPlanContentTemple();
            case 'my_task':
                return $this->getMyTaskTemple();
            case 'exam':
                return $this->getExamTemple();
            case 'qa':
                return $this->getQATemple();
            default:
                return array();
        }
    }

    protected function getEnrollTemplate()
    {
        return array(
            'title' => 'admin.mail_setting.mail_notification.preview.temp.enroll_result',
            'templates' => array(
                'offlineActivityAddMember' => array(
                    'title' => 'admin.mail_setting.mail_notification.preview.temp.enroll_result.1.title',
                    'message' => 'admin.mail_setting.mail_notification.preview.temp.enroll_result.1.message',
                ),
                'offlineActivityApproveReject' => array(
                    'title' => 'admin.mail_setting.mail_notification.preview.temp.enroll_result.2.title',
                    'message' => 'admin.mail_setting.mail_notification.preview.temp.enroll_result.2.message',
                ),
                'projectPlanEnrollResult' => array(
                    'title' => 'admin.mail_setting.mail_notification.preview.temp.enroll_result.3.title',
                    'message' => 'admin.mail_setting.mail_notification.preview.temp.enroll_result.3.message',
                ),
                'projectPlanApproveReject' => array(
                    'title' => 'admin.mail_setting.mail_notification.preview.temp.enroll_result.4.title',
                    'message' => 'admin.mail_setting.mail_notification.preview.temp.enroll_result.4.message',
                ),
            ),
        );
    }

    protected function getPostAssignTemple()
    {
        return array(
            'title' => 'admin.mail_setting.mail_notification.preview.temp.post_course_update',
            'templates' => array(
                'postCourseAdd' => array(
                    'title' => 'admin.mail_setting.mail_notification.preview.temp.post_course_update.1.title',
                    'message' => 'admin.mail_setting.mail_notification.preview.temp.post_course_update.1.message',
                ),
            ),
        );
    }

    protected function getProjectPlanProgressTemple()
    {
        return array(
            'title' => 'admin.mail_setting.mail_notification.preview.temp.project',
            'templates' => array(
                'projectPlanOnlineExamStart' => array(
                    'title' => 'admin.mail_setting.mail_notification.preview.temp.project.1.title',
                    'message' => 'admin.mail_setting.mail_notification.preview.temp.project.1.message',
                ),
                'projectPlanOfflineExamStart' => array(
                    'title' => 'admin.mail_setting.mail_notification.preview.temp.project.2.title',
                    'message' => 'admin.mail_setting.mail_notification.preview.temp.project.2.message',
                ),
                'offlineCourseTask' => array(
                    'title' => 'admin.mail_setting.mail_notification.preview.temp.project.3.title',
                    'message' => 'admin.mail_setting.mail_notification.preview.temp.project.3.message',
                ),
                'offlineCourseQuestionnaire' => array(
                    'title' => 'admin.mail_setting.mail_notification.preview.temp.project.4.title',
                    'message' => 'admin.mail_setting.mail_notification.preview.temp.project.4.message',
                ),
            ),
        );
    }

    protected function getLiveCourseTemple()
    {
        return array(
            'title' => 'admin.mail_setting.mail_notification.preview.live_course',
            'templates' => array(
                'projectPlanUpdate' => array(
                    'title' => 'admin.mail_setting.mail_notification.preview.live_course.1.title',
                    'message' => 'admin.mail_setting.mail_notification.preview.live_course.1.message',
                ),
            ),
        );
    }

    protected function getProjectPlanContentTemple()
    {
        return array(
            'title' => 'admin.mail_setting.mail_notification.preview.project_plan_content',
            'templates' => array(
                'projectPlanUpdate' => array(
                    'title' => 'admin.mail_setting.mail_notification.preview.project_plan_content.1.title',
                    'message' => 'admin.mail_setting.mail_notification.preview.project_plan_content.1.message',
                ),
            ),
        );
    }

    protected function getMyTaskTemple()
    {
        return array(
            'title' => 'admin.mail_setting.mail_notification.preview.my_task',
            'templates' => array(
                'examAssign' => array(
                    'title' => 'admin.mail_setting.mail_notification.preview.my_task.1.title',
                    'message' => 'admin.mail_setting.mail_notification.preview.my_task.1.message',
                ),
                'projectPlanAssign' => array(
                    'title' => 'admin.mail_setting.mail_notification.preview.my_task.2.title',
                    'message' => 'admin.mail_setting.mail_notification.preview.my_task.2.message',
                ),
            ),
        );
    }

    protected function getExamTemple()
    {
        return array(
            'title' => 'admin.mail_setting.mail_notification.preview.exam',
            'templates' => array(
                'examResult' => array(
                    'title' => 'admin.mail_setting.mail_notification.preview.exam.1.title',
                    'message' => 'admin.mail_setting.mail_notification.preview.exam.1.message',
                ),
            ),
        );
    }

    protected function getQATemple()
    {
        return array(
            'title' => 'admin.mail_setting.mail_notification.preview.qa',
            'templates' => array(
                'qaPostCreate' => array(
                    'title' => 'admin.mail_setting.mail_notification.preview.qa.1.title',
                    'message' => 'admin.mail_setting.mail_notification.preview.qa.1.message',
                ),
                'qaPostFollow' => array(
                    'title' => 'admin.mail_setting.mail_notification.preview.qa.2.title',
                    'message' => 'admin.mail_setting.mail_notification.preview.qa.2.message',
                ),
            ),
        );
    }

    /**
     * @return SettingService
     */
    private function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
