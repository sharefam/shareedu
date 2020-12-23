<?php

namespace CorporateTrainingBundle\Biz\OfflineActivity\Event;

use Biz\Sms\Service\SmsService;
use Biz\System\Service\SettingService;
use Biz\User\Service\NotificationService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Queue\Service\QueueService;
use Codeages\PluginBundle\Event\EventSubscriber;
use CorporateTrainingBundle\Biz\OfflineActivity\Service\EnrollmentRecordService;
use CorporateTrainingBundle\Biz\OfflineActivity\Service\OfflineActivityService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OfflineActivityMemberEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'offline.create.member' => 'onCreateMember',
            'offline.member.delete' => 'onMemberDelete',
            'offline.reject.apply' => 'onRejectApply',
            'offline.member.enter' => 'onMemberEnter',
            'offline_activity_batch_become_member' => 'onBatchBecomeMember',
        );
    }

    public function onCreateMember(Event $event)
    {
        $member = $event->getSubject();
        $offlineActivity = $this->getOfflineActivityService()->getOfflineActivity($member['offlineActivityId']);

        if (empty($member) || empty($offlineActivity)) {
            return;
        }

        $this->smsBecomeMember($member['userId'], $offlineActivity);

        $this->notifyBecomeMember($member['userId'], $offlineActivity);
    }

    public function onMemberEnter(Event $event)
    {
        $member = $event->getSubject();
        $offlineActivity = $this->getOfflineActivityService()->getOfflineActivity($member['offlineActivityId']);

        if (empty($member) || empty($offlineActivity)) {
            return;
        }

        $this->sendNotification(array($member['userId']), $offlineActivity, true);
    }

    protected function smsBecomeMember($userId, $offlineActivity)
    {
        $smsType = 'sms_activity_signup';

        $isOpen = $this->getSmsService()->isOpen($smsType);
        if (!$isOpen) {
            return;
        }

        $parameters = array(
            'campaign' => $offlineActivity['title'],
            'date' => date('Y-m-d H:i', $offlineActivity['startTime']),
            'address' => $offlineActivity['address'],
        );

        $this->getSmsService()->smsSend($smsType, array($userId), array(), $parameters);
    }

    protected function notifyBecomeMember($userId, $offlineActivity)
    {
        $this->getNotificationService()->notify($userId, 'offline_activity_become_member', $offlineActivity);
    }

    public function onRejectApply(Event $event)
    {
        $record = $event->getSubject();
        $offlineActivity = $this->getOfflineActivityService()->getOfflineActivity($record['offlineActivityId']);

        if (empty($offlineActivity) || empty($record)) {
            return;
        }

        $this->smsRejectApply($record['userId'], $offlineActivity, $record);

        $this->notifyRejectApply($record['userId'], $offlineActivity, $record);
    }

    protected function smsRejectApply($userId, $offlineActivity, $record)
    {
        $smsType = 'sms_activity_signup_fail';

        $isOpen = $this->getSmsService()->isOpen($smsType);
        if (!$isOpen) {
            return;
        }

        $parameters = array(
            'campaign' => $offlineActivity['title'],
            'reason' => $record['rejectedReason'],
        );

        $this->getSmsService()->smsSend($smsType, array($userId), array(), $parameters);
    }

    protected function notifyRejectApply($userId, $offlineActivity, $record)
    {
        $content['offlineActivity'] = $offlineActivity;
        $content['record'] = $record;
        $this->getNotificationService()->notify($userId, 'offline_activity_enroll_rejected', $content);
    }

    public function onMemberDelete(Event $event)
    {
        $member = $event->getSubject();
        $this->getEnrollmentRecordService()->deleteEnrollmentRecordByActivityIdAndUserId($member['offlineActivityId'], $member['userId']);
        $this->getOfflineActivityService()->refreshMemberCount($member['offlineActivityId']);
    }

    public function onBatchBecomeMember(Event $event)
    {
        $content = $event->getSubject();
        $userIds = $content['userIds'];
        $activity = $content['activity'];

        $this->sendNotification($userIds, $activity);
    }

    protected function sendNotification($userIds, $activity, $hasDingtalk = false)
    {
        if (empty($userIds)) {
            return;
        }
        $mailNotification = $this->getSettingService()->get('mail_notification', array());
        if (!empty($mailNotification['enroll'])) {
            $types[] = 'email';
        }

        $dingtalkNotification = $this->getSettingService()->get('dingtalk_notification', array());
        if (!empty($dingtalkNotification['offline_activity_apply']) && $hasDingtalk) {
            $types[] = 'dingtalk';
        }

        if (empty($types)) {
            return;
        }

        global $kernel;
        $url = $kernel->getContainer()->get('router')->generate('offline_activity_detail', array('id' => $activity['id']), true);

        $to = array(
            'type' => 'user',
            'userIds' => $userIds,
            'startNum' => 0,
            'perPageNum' => 20,
        );

        foreach ($types as $type) {
            $templates = array(
                'email' => 'offline_activity_add_member',
                'dingtalk' => 'offline_activity_apply',
            );
            $content = array(
                'template' => $templates[$type],
                'params' => array(
                    'url' => $url,
                    'offlineActivityTitle' => $activity['title'],
                    'batch' => $templates[$type].$activity['id'].time(),
                    'targetId' => $activity['id'],
                    'imagePath' => empty($activity['cover']['large']) ? '' : $activity['cover']['large'],
                ),
            );
            $this->getBiz()->offsetGet('notification_'.$type)->send($to, $content);
        }
    }

    /**
     * @return SmsService
     */
    protected function getSmsService()
    {
        return $this->getBiz()->service('Sms:SmsService');
    }

    /**
     * @return OfflineActivityService
     */
    protected function getOfflineActivityService()
    {
        return $this->getBiz()->service('CorporateTrainingBundle:OfflineActivity:OfflineActivityService');
    }

    /**
     * @return EnrollmentRecordService
     */
    protected function getEnrollmentRecordService()
    {
        return $this->getBiz()->service('CorporateTrainingBundle:OfflineActivity:EnrollmentRecordService');
    }

    /**
     * @return NotificationService
     */
    protected function getNotificationService()
    {
        return $this->getBiz()->service('User:NotificationService');
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
}
