<?php

namespace CorporateTrainingBundle\Biz\DingTalk\Template;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Exception\InvalidArgumentException;

class OfflineActivityApplyTemplate extends BaseTemplate implements DingTalkTemplateInterface
{
    protected $type = 'offline_activity_apply';

    protected $linkUrl = '';

    protected $markdownContent = '';

    protected $title = '报名结果通知';

    /**
     * {@inheritdoc}
     */
    public function parse($options)
    {
        if (!ArrayToolkit::requireds($options, array('targetId', 'imagePath', 'url', 'offlineActivityTitle', 'batch'))) {
            throw new InvalidArgumentException('Lack of required fields');
        }
        $imgUrl = $this->getFileUrl($options['imagePath'], 'activity.png');
        $this->linkUrl = isset($options['url']) ? $options['url'] : $this->getBaseUrl().'/';
        $this->markdownContent = "#### 报名结果通知  \n  ";
        $this->markdownContent .= "恭喜你成功报名线下活动  \n  ";
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
