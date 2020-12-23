<?php

namespace CorporateTrainingBundle\Biz\OfflineActivity\Event;

use Biz\System\Service\SettingService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OfflineActivityEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'offline_activity.publish' => 'OnOfflineActivityUpdate',
            'offline_activity.update' => 'OnOfflineActivityUpdate',
            'offline_activity.close' => 'OnOfflineActivityClose',
        );
    }

    public function OnOfflineActivityUpdate(Event $event)
    {
        $offlineActivity = $event->getSubject();
        $this->registerSendNotificationJob($offlineActivity);
    }

    public function OnOfflineActivityClose(Event $event)
    {
        $offlineActivity = $event->getSubject();
        $this->deleteExistedJob($offlineActivity);
    }

    protected function deleteExistedJob($offlineActivity)
    {
        $this->getSchedulerService()->deleteJobByName('OfflineActivityDayDingtalkRemindNotificationJob_'.$offlineActivity['id']);
        $this->getSchedulerService()->deleteJobByName('OfflineActivityHourDingtalkRemindNotificationJob_'.$offlineActivity['id']);
    }

    protected function registerSendNotificationJob($offlineActivity)
    {
        $this->deleteExistedJob($offlineActivity);

        if ('published' !== $offlineActivity['status']) {
            return;
        }

        $dingtalkNotification = $this->getSettingService()->get('dingtalk_notification', array());

        $currentTime = time();
        global $kernel;
        $url = $kernel->getContainer()->get('router')->generate('offline_activity_detail', array('id' => $offlineActivity['id']), true);

        if (!empty($dingtalkNotification['offline_activity_hour_remind']) && $offlineActivity['startTime'] - $currentTime >= 30 * 60) {
            $job = array(
                'name' => 'OfflineActivityHourDingtalkRemindNotificationJob_'.$offlineActivity['id'],
                'source' => 'TRAININGMAIN',
                'expression' => $offlineActivity['startTime'] - 60 * 60,
                'class' => 'CorporateTrainingBundle\Biz\OfflineActivity\Job\OfflineActivityDingTalkNotificationJob',
                'args' => array(
                    'notificationType' => 'offline_activity_hour_remind',
                    'offlineActivity' => $offlineActivity,
                    'template' => 'offline_activity_remind',
                    'url' => $url,
                ),
                'misfire_policy' => 'executing',
            );

            $this->getSchedulerService()->register($job);
        }

        if (!empty($dingtalkNotification['offline_activity_day_remind'])) {
            $executeTime = strtotime('-1 day 08:00:00', $offlineActivity['startTime']);
            $todayExecuteTime = strtotime('08:00:00', $currentTime);

            if ($executeTime <= $todayExecuteTime && $currentTime > $todayExecuteTime) {
                return;
            }

            $job = array(
                'name' => 'OfflineActivityDayDingtalkRemindNotificationJob_'.$offlineActivity['id'],
                'source' => 'TRAININGMAIN',
                'expression' => $executeTime,
                'class' => 'CorporateTrainingBundle\Biz\OfflineActivity\Job\OfflineActivityDingTalkNotificationJob',
                'args' => array(
                    'notificationType' => 'offline_activity_day_remind',
                    'offlineActivity' => $offlineActivity,
                    'template' => 'offline_activity_remind',
                    'url' => $url,
                ),
                'misfire_policy' => 'executing',
            );

            $this->getSchedulerService()->register($job);
        }
    }

    /**
     * @return SchedulerService
     */
    protected function getSchedulerService()
    {
        return $this->getBiz()->service('Scheduler:SchedulerService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }
}
