<?php

namespace CorporateTrainingBundle\Biz\OfflineActivity\Event;

use AppBundle\Common\ArrayToolkit;
use Biz\System\Service\SettingService;
use Biz\User\Service\UserService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Event\EventSubscriber;
use CorporateTrainingBundle\Biz\OfflineActivity\Service\EnrollmentRecordService;
use CorporateTrainingBundle\Biz\OfflineActivity\Service\OfflineActivityService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EnrollmentRecordEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'offline_activity.batch.pass' => 'onOfflineActivityBatchPass',
            'offline_activity.batch.reject' => 'onOfflineActivityBatchReject',
        );
    }

    public function onOfflineActivityBatchPass(Event $event)
    {
        $recordIds = $event->getSubject();
        $records = $this->getEnrollmentRecordService()->findEnrollmentRecordsByIds($recordIds);
        $offlineActivity = $this->getOfflineActivityService()->getOfflineActivity($records[0]['offlineActivityId']);
        $userIds = ArrayToolkit::column($records, 'userId');
        $this->sendNotificationEmail('offline_activity_apply', $userIds, $offlineActivity);
    }

    public function onOfflineActivityBatchReject(Event $event)
    {
        $content = $event->getSubject();
        $recordIds = $content['recordIds'];
        $rejectReason = $content['rejectReason'];
        $records = $this->getEnrollmentRecordService()->findEnrollmentRecordsByIds($recordIds);
        $offlineActivity = $this->getOfflineActivityService()->getOfflineActivity($records[0]['offlineActivityId']);
        $userIds = ArrayToolkit::column($records, 'userId');
        $this->sendNotificationEmail('offline_activity_approve_reject', $userIds, $offlineActivity, $rejectReason);
    }

    protected function sendNotificationEmail($template, $userIds, $offlineActivity, $rejectReason = '')
    {
        if (empty($userIds)) {
            return;
        }
        $mailNotification = $this->getSettingService()->get('mail_notification', array());
        $dingtalkNotification = $this->getSettingService()->get('dingtalk_notification', array());
        if (!empty($mailNotification['enroll'])) {
            $types[] = 'email';
        }

        if (!empty($dingtalkNotification['offline_activity_apply'])) {
            $types[] = 'dingtalk';
        }
        if (empty($types)) {
            return;
        }

        global $kernel;
        $url = $kernel->getContainer()->get('router')->generate('offline_activity_detail', array('id' => $offlineActivity['id']), true);

        $to = array(
            'type' => 'user',
            'userIds' => $userIds,
            'startNum' => 0,
            'perPageNum' => 20,
        );

        $content = array(
            'template' => $template,
            'params' => array(
                'batch' => $template.$offlineActivity['id'].time(),
                'targetId' => $offlineActivity['id'],
                'offlineActivityTitle' => $offlineActivity['title'],
                'url' => $url,
                'imagePath' => empty($offlineActivity['cover']['large']) ? '' : $offlineActivity['cover']['large'],
                'rejectedReason' => $rejectReason,
            ),
        );

        $this->getBiz()->offsetGet('notification_default')->send($types, $to, $content);
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
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }
}
