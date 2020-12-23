<?php

namespace CorporateTrainingBundle\Biz\NotificationCenter\Strategy\Notification\Impl;

use CorporateTrainingBundle\Biz\NotificationCenter\QueueJob\EmailNotificationJob;
use CorporateTrainingBundle\Biz\NotificationCenter\Strategy\Notification\Notification;
use CorporateTrainingBundle\Biz\NotificationCenter\Strategy\Notification\NotificationStrategy;

class EmailNotificationStrategy extends NotificationStrategy implements Notification
{
    public function send($to, $content)
    {
        $emailNotificationJob = new EmailNotificationJob(
            array(
                'to' => $to,
                'content' => $content,
            ),
            array(
                'timeout' => 300,
            )
        );
        $this->getQueueService()->pushJob($emailNotificationJob, 'database');
    }
}
