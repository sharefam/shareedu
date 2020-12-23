<?php

namespace CorporateTrainingBundle\Biz\ProjectPlan\Event;

use Biz\System\Service\SettingService;
use Codeages\Biz\Framework\Event\EventSubscriber;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Queue\Service\QueueService;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use CorporateTrainingBundle\Biz\ProjectPlan\Service\ProjectPlanService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProjectPlanItemEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'project_plan.create.offline_exam' => 'onProjectPlanCreateOfflineExam',
            'project_plan.update.offline_exam' => 'onProjectPlanCreateOfflineExam',
            'project_plan.delete.offline_exam' => 'onProjectPlanDeleteOfflineExam',
        );
    }

    public function onProjectPlanCreateOfflineExam(Event $event)
    {
        $exam = $event->getSubject();
        $this->deleteJobByExam($exam);
        $this->registerSendNotificationJob($exam);
    }

    public function onProjectPlanDeleteOfflineExam(Event $event)
    {
        $exam = $event->getSubject();
        $this->deleteJobByExam($exam);
    }

    protected function deleteJobByExam($exam)
    {
        $this->getSchedulerService()->deleteJobByName('OfflineExamMailNotificationJob_'.$exam['id']);
        $this->getSchedulerService()->deleteJobByName('OfflineExamOneDayDingtalkRemindNotificationJob_'.$exam['id']);
        $this->getSchedulerService()->deleteJobByName('OfflineExamOneHourDingtalkRemindNotificationJob_'.$exam['id']);
    }

    protected function registerSendNotificationJob($exam)
    {
        $mailNotification = $this->getSettingService()->get('mail_notification', array());
        $dingtalkNotification = $this->getSettingService()->get('dingtalk_notification', array());
        $projectPlan = $this->getProjectPlanService()->getProjectPlan($exam['projectPlanId']);

        if (!empty($mailNotification['project_plan_progress'])) {
            $this->registerMailNotificationJob($projectPlan, $exam);
        }

        if (!empty($dingtalkNotification['project_plan_offline_exam_day_remind'])) {
            $this->registerOneDayDingtalkRemindJob($projectPlan, $exam);
        }

        if (!empty($dingtalkNotification['project_plan_offline_exam_hour_remind'])) {
            $this->registerOneHourDingtalkRemindJob($projectPlan, $exam);
        }
    }

    protected function registerMailNotificationJob($projectPlan, $exam)
    {
        global $kernel;

        $executeTime = ($exam['startTime'] - time()) >= 24 * 60 * 60 ? strtotime('-1 day', $exam['startTime']) : time();
        $job = array(
            'name' => 'OfflineExamMailNotificationJob_'.$exam['id'],
            'source' => 'TRAININGMAIN',
            'expression' => $executeTime,
            'class' => 'CorporateTrainingBundle\Biz\ProjectPlan\Job\ProjectPlanMailNotificationJob',
            'args' => array(
                'projectPlanId' => $exam['projectPlanId'],
                'template' => 'project_plan_create_offline_exam',
                'params' => array(
                    'projectPlanName' => $projectPlan['name'],
                    'examName' => $exam['title'],
                    'place' => $exam['place'],
                    'startTime' => date('Y-m-d H:i:s', $exam['startTime']),
                    'url' => $kernel->getContainer()->get('router')->generate('study_center_my_task_training_list', array(), true),
                ),
            ),
            'misfire_policy' => 'executing',
        );

        $this->getSchedulerService()->register($job);
    }

    protected function registerOneDayDingtalkRemindJob($projectPlan, $exam)
    {
        $currentTime = time();
        $executeTime = strtotime('-1 day 08:00:00', $exam['startTime']);
        $todayExecuteTime = strtotime('08:00:00', $currentTime);

        if ($executeTime <= $todayExecuteTime && $currentTime > $todayExecuteTime) {
            return;
        }

        global $kernel;
        $job = array(
            'name' => 'OfflineExamOneDayDingtalkRemindNotificationJob_'.$exam['id'],
            'source' => 'TRAININGMAIN',
            'expression' => $executeTime,
            'class' => 'CorporateTrainingBundle\Biz\OfflineExam\Job\OfflineExamDingTalkNotificationJob',
            'args' => array(
                'notificationType' => 'project_plan_offline_exam_day_remind',
                'exam' => $exam,
                'template' => 'offline_exam_remind',
                'url' => $kernel->getContainer()->get('router')->generate('project_plan_detail', array('id' => $projectPlan['id']), true),
            ),
            'misfire_policy' => 'executing',
        );

        $this->getSchedulerService()->register($job);
    }

    protected function registerOneHourDingtalkRemindJob($projectPlan, $exam)
    {
        if ($exam['startTime'] - time() < 1 * 60 * 60) {
            return;
        }

        global $kernel;
        $job = array(
            'name' => 'OfflineExamOneHourDingtalkRemindNotificationJob_'.$exam['id'],
            'source' => 'TRAININGMAIN',
            'expression' => $exam['startTime'] - 60 * 60,
            'class' => 'CorporateTrainingBundle\Biz\OfflineExam\Job\OfflineExamDingTalkNotificationJob',
            'args' => array(
                'notificationType' => 'project_plan_offline_exam_hour_remind',
                'exam' => $exam,
                'template' => 'offline_exam_remind',
                'url' => $kernel->getContainer()->get('router')->generate('project_plan_detail', array('id' => $projectPlan['id']), true),
            ),
            'misfire_policy' => 'executing',
        );

        $this->getSchedulerService()->register($job);
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    /**
     * @return QueueService
     */
    protected function getQueueService()
    {
        return $this->getBiz()->service('Queue:QueueService');
    }

    /**
     * @return SchedulerService
     */
    protected function getSchedulerService()
    {
        return $this->getBiz()->service('Scheduler:SchedulerService');
    }

    /**
     * @return ProjectPlanService
     */
    protected function getProjectPlanService()
    {
        return $this->getBiz()->service('CorporateTrainingBundle:ProjectPlan:ProjectPlanService');
    }
}
