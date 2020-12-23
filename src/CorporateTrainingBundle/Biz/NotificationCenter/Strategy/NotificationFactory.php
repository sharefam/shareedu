<?php

namespace CorporateTrainingBundle\Biz\NotificationCenter\Strategy;

use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;

class NotificationFactory
{
    protected $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    public function createNotification($type)
    {
        $notificationType = $this->getNotificationType($type);

        if (empty($this->biz->offsetGet($notificationType))) {
            throw new NotFoundException("Notification strategy {$notificationType} does not exist");
        }

        return $this->biz->offsetGet($notificationType);
    }

    protected function getNotificationType($type)
    {
        return 'notification_'.$type;
    }
}
