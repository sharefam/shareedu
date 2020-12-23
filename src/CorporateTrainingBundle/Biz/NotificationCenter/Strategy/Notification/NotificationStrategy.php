<?php

namespace CorporateTrainingBundle\Biz\NotificationCenter\Strategy\Notification;

use Biz\System\Service\LogService;
use Biz\System\Service\SettingService;
use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Queue\Service\QueueService;

class NotificationStrategy
{
    protected $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    /**
     * @return QueueService
     */
    protected function getQueueService()
    {
        return $this->createService('Queue:QueueService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    protected function createService($alias)
    {
        return $this->biz->service($alias);
    }

    protected function createDao($alias)
    {
        return $this->biz->service($alias);
    }
}
