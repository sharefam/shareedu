<?php

namespace CorporateTrainingBundle\Biz\NotificationCenter\Strategy\Notification;

interface Notification
{
    /**
     * @param $to array 目标收件人
     * @param $content array 邮件模板内容
     */
    public function send($to, $content);
}
