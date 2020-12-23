<?php

namespace CorporateTrainingBundle\Biz\DingTalk\Job;

use AppBundle\Common\ArrayToolkit;
use Biz\System\Service\SettingService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

abstract class AbstractDingTalkNotificationJob extends AbstractJob
{
    public function execute()
    {
        if (!ArrayToolkit::requireds($this->args, array('notificationType', 'template'))) {
            throw new \InvalidArgumentException('Lack of required fields');
        }

        $dingtalkNotification = $this->getSettingService()->get('dingtalk_notification', array());

        if (empty($dingtalkNotification[$this->args['notificationType']])) {
            return true;
        }

        if ($this->canSend()) {
            list($to, $content) = $this->buildNotificationData();
            $this->biz->offsetGet('notification_default')->send(array('dingtalk'), $to, $content);
        }

        return true;
    }

    protected function canSend()
    {
        return true;
    }

    /**
     * @return array($to, $content)
     */
    abstract protected function buildNotificationData();

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }
}
