<?php

namespace CorporateTrainingBundle\Biz\NotificationCenter\Strategy\Notification\Impl;

use CorporateTrainingBundle\Biz\NotificationCenter\Strategy\Notification\NotificationStrategy;

class DefaultNotificationStrategy extends NotificationStrategy
{
    public function send($types, $to, $content)
    {
        foreach ($types as $type) {
            if (isset($this->biz['notification_'.$type])) {
                $this->biz['notification_'.$type]->send($to, $content);
            }
        }
    }
}
