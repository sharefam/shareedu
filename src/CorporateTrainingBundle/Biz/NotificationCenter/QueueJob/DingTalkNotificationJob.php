<?php

namespace CorporateTrainingBundle\Biz\NotificationCenter\QueueJob;

use Biz\System\Service\LogService;
use Codeages\Biz\Framework\Queue\AbstractJob;
use Codeages\Biz\Framework\Queue\Service\QueueService;
use CorporateTrainingBundle\Biz\DingTalk\Service\Impl\DingTalkServiceImpl;
use Topxia\Service\Common\ServiceKernel;

class DingTalkNotificationJob extends AbstractJob
{
    public function execute()
    {
        $context = $this->getBody();
        $to = $context['to'];
        $content = $context['content'];

        $group = $this->biz->offsetGet('notification_user_group_'.$to['type']);
        $dingTalkGroup = $group->findGroupDingTalkUsers($to);
        try {
            $notificationOptions = array(
                'to' => $dingTalkGroup['ids'],
                'targetType' => $content['template'],
                'params' => $content['params'],
                'type' => 'notification',
            );
            $template = $this->parseTemplate($content['template'], $content['params']);
            $this->getDingTalkService()->sendDingTalkNotification($template, $dingTalkGroup['ids']);

            if ($dingTalkGroup['params']['startNum'] < $dingTalkGroup['num']) {
                $dingTalkNotificationJob = new self(
                    array(
                        'to' => $dingTalkGroup['params'],
                        'content' => $content,
                    )
                );
                $this->getQueueService()->pushJob($dingTalkNotificationJob, 'database');
            }
        } catch (\Exception $e) {
            $message = $e->getMessage().serialize($notificationOptions);
            $this->getLogService()->error('dingtalk', 'dingtalk_notification', '钉钉通知发送失败:'.$message);
        }
    }

    protected function parseTemplate($templateType, $params)
    {
        $biz = ServiceKernel::instance()->getBiz();

        return $biz['dingtalk_template_parser']->parseTemplate($templateType, $params);
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->biz->service('System:LogService');
    }

    /**
     * @return DingTalkServiceImpl
     */
    protected function getDingTalkService()
    {
        return $this->biz->service('CorporateTrainingBundle:DingTalk:DingTalkService');
    }

    /**
     * @return QueueService
     */
    protected function getQueueService()
    {
        return $this->biz->service('Queue:QueueService');
    }
}
