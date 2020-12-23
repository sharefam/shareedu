<?php

namespace CorporateTrainingBundle\Biz\NotificationCenter\QueueJob;

use Biz\System\Service\LogService;
use Codeages\Biz\Framework\Queue\AbstractJob;
use Codeages\Biz\Framework\Queue\Service\QueueService;

class EmailNotificationJob extends AbstractJob
{
    public function execute()
    {
        $context = $this->getBody();
        $to = $context['to'];
        $content = $context['content'];

        $group = $this->biz->offsetGet('notification_user_group_'.$to['type']);
        $emailGroup = $group->findGroupEmails($to);

        try {
            $mailOptions = array(
                'to' => $emailGroup['emails'],
                'template' => $content['template'],
                'params' => $content['params'],
                'type' => 'notification',
            );

            $mailFactory = $this->biz->offsetGet('ct_mail_factory');

            $mail = $mailFactory($mailOptions);
            $mail->send();

            if ($emailGroup['params']['startNum'] < $emailGroup['num']) {
                $emailNotificationJob = new self(
                    array(
                        'to' => $emailGroup['params'],
                        'content' => $content,
                    )
                );
                $this->getQueueService()->pushJob($emailNotificationJob, 'database');
            }
        } catch (\Exception $e) {
            $this->getLogService()->error('mail_notification', 'mail_notification', '通知邮件发送发送失败:'.$e->getMessage().serialize($mailOptions));
        }
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->biz->service('System:LogService');
    }

    /**
     * @return QueueService
     */
    protected function getQueueService()
    {
        return $this->biz->service('Queue:QueueService');
    }
}
