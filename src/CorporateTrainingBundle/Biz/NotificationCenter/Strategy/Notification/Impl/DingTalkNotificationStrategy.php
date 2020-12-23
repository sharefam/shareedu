<?php

namespace CorporateTrainingBundle\Biz\NotificationCenter\Strategy\Notification\Impl;

use CorporateTrainingBundle\Biz\NotificationCenter\QueueJob\DingTalkNotificationJob;
use CorporateTrainingBundle\Biz\NotificationCenter\Strategy\Notification\Notification;
use CorporateTrainingBundle\Biz\NotificationCenter\Strategy\Notification\NotificationStrategy;

class DingTalkNotificationStrategy extends NotificationStrategy implements Notification
{
    public function send($to, $content)
    {
        $setting = $this->getSettingService()->get('sync_department_setting', array());
        if (empty($setting['enable'])) {
            return true;
        }
        $context = array(
            'to' => $to,
            'content' => $content,
        );
        $dingTalkNotificationJob = new DingTalkNotificationJob(
            $context,
            array(
                'timeout' => 300,
            )
        );
        $this->getQueueService()->pushJob($dingTalkNotificationJob, 'database');
    }
}
