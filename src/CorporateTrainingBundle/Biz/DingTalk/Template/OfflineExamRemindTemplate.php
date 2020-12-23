<?php

namespace CorporateTrainingBundle\Biz\DingTalk\Template;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Exception\InvalidArgumentException;

class OfflineExamRemindTemplate extends BaseTemplate implements DingTalkTemplateInterface
{
    protected $type = 'project_plan_offline_exam_hour_remind';

    protected $title = '线下考试参加提醒';

    protected $linkTitle = '查看详情';

    public function parse($options)
    {
        if (!ArrayToolkit::requireds($options, array('targetId', 'batch', 'url', 'title', 'startTime', 'endTime', 'place'))) {
            throw new InvalidArgumentException('Lack of required fields');
        }
        $imgUrl = $this->getBaseUrl().'/assets/img/dingtalk/exam.png';
        $this->linkUrl = isset($options['url']) ? $options['url'] : $this->getBaseUrl().'/';
        $this->markdownContent = "#### 线下考试参加提醒  \n  你参加的线下考试即将开始，请准时参加  \n  ";
        $this->markdownContent .= "![]({$imgUrl})  \n  ";
        $this->markdownContent .= "考试名称：{$options['title']}  \n  ";
        $this->markdownContent .= '开考时间：'.date('Y-m-d H:i:s', $options['startTime']).'至'.date('Y-m-d H:i:s', $options['endTime'])."  \n  ";
        $this->markdownContent .= "考试地点：{$options['place']}  \n  ";
        $this->markdownContent .= '###### 发送时间：'.date('Y-m-d H:i:s', time());

        return array(
            'targetType' => $this->type,
            'targetId' => $options['targetId'],
            'batch' => $options['batch'],
            'message' => $this->makeSingleContent(),
        );
    }
}
