<?php

namespace CorporateTrainingBundle\Biz\DingTalk\Template;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Exception\InvalidArgumentException;

class OfflineActivityApproveRejectTemplate extends BaseTemplate implements DingTalkTemplateInterface
{
    protected $type = 'offline_activity_approve_reject';

    protected $linkUrl = '';

    protected $markdownContent = '';

    protected $title = '报名结果通知';

    /**
     * {@inheritdoc}
     */
    public function parse($options)
    {
        if (!ArrayToolkit::requireds($options, array('targetId', 'batch', 'imagePath', 'url', 'offlineActivityTitle', 'rejectedReason'))) {
            throw new InvalidArgumentException('Lack of required fields');
        }
        $imgUrl = $this->getFileUrl($options['imagePath'], 'activity.png');
        $this->linkUrl = isset($options['url']) ? $options['url'] : $this->getBaseUrl().'/';
        $this->markdownContent = "#### 报名结果通知  \n  ";
        $this->markdownContent .= "你的报名未通过审核  \n  ";
        $this->markdownContent .= "原因：{$options['rejectedReason']}  \n  ";
        $this->markdownContent .= "![]({$imgUrl})  \n  ";
        $this->markdownContent .= "活动名称：{$options['offlineActivityTitle']}  \n  ";
        $this->markdownContent .= '###### 发送时间：'.date('Y-m-d H:i:s', time());

        return array(
            'targetType' => $this->type,
            'targetId' => $options['targetId'],
            'batch' => $options['batch'],
            'message' => $this->makeSingleContent(),
        );
    }
}
