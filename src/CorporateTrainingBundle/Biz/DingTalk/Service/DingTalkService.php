<?php

namespace CorporateTrainingBundle\Biz\DingTalk\Service;

interface DingTalkService
{
    /**
     * @param array $template //钉钉场景模版
     * @param array $userIds  //用户id
     *
     * @return bool
     */
    public function sendDingTalkNotification($userIds, $template);
}
