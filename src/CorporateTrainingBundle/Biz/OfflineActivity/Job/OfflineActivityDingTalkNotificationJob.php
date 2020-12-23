<?php

namespace CorporateTrainingBundle\Biz\OfflineActivity\Job;

use CorporateTrainingBundle\Biz\DingTalk\Job\AbstractDingTalkNotificationJob;

class OfflineActivityDingTalkNotificationJob extends AbstractDingTalkNotificationJob
{
    protected function buildNotificationData()
    {
        $offlineActivity = $this->args['offlineActivity'];
        $to = array(
            'type' => 'offline_activity',
            'offlineActivityId' => $offlineActivity['id'],
            'startNum' => 0,
            'perPageNum' => 20,
        );
        $content = array(
            'template' => $this->args['template'],
            'params' => array(
                'targetId' => $offlineActivity['id'],
                'batch' => 'offline_activity_remind'.$offlineActivity['id'].time(),
                'url' => $this->args['url'],
                'title' => $offlineActivity['title'],
                'place' => $offlineActivity['address'],
                'startTime' => $offlineActivity['startTime'],
                'endTime' => $offlineActivity['endTime'],
                'cover' => empty($offlineActivity['cover']['large']) ? '' : $offlineActivity['cover']['large'],
            ),
        );

        return array($to, $content);
    }
}
