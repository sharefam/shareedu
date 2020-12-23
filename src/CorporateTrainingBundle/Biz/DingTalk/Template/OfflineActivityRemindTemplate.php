<?php

namespace CorporateTrainingBundle\Biz\DingTalk\Template;

use AppBundle\Common\ArrayToolkit;

class OfflineActivityRemindTemplate extends BaseTemplate implements DingTalkTemplateInterface
{
    protected $type = 'offline_activity_remind';

    protected $title = '活动参加提醒';

    protected $linkTitle = '查看详情';

    public function parse($options)
    {
        if (!ArrayToolkit::requireds($options, array('targetId', 'batch', 'url', 'title', 'place', 'startTime', 'endTime', 'cover'))) {
            throw new \InvalidArgumentException('Lack of required fields');
        }

        $this->linkUrl = isset($options['url']) ? $options['url'] : $this->getBaseUrl().'/';
        $imgUrl = $this->getFileUrl($options['cover'], 'activity.png');

        $this->markdownContent = "#### 活动参加提醒  \n  你参加的活动即将开始，请准时参加  \n  ";
        $this->markdownContent .= "![]({$imgUrl})  \n  ";
        $this->markdownContent .= "活动名称：{$options['title']}  \n  ";
        $this->markdownContent .= '开始时间：'.date('Y-m-d H:i:s', $options['startTime'])."  \n  ";
        $this->markdownContent .= "活动地点：{$options['place']}  \n  ";
        $this->markdownContent .= '###### 发送时间：'.date('Y-m-d H:i:s', time());

        return array(
            'targetType' => $this->type,
            'targetId' => $options['targetId'],
            'batch' => $options['batch'],
            'message' => $this->makeSingleContent(),
        );
    }
}
