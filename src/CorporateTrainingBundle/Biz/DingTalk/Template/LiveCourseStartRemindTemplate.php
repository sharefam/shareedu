<?php

namespace CorporateTrainingBundle\Biz\DingTalk\Template;

use AppBundle\Common\ArrayToolkit;

class LiveCourseStartRemindTemplate extends BaseTemplate implements DingTalkTemplateInterface
{
    protected $type = 'live_course_start_remind';

    protected $title = '你的直播课程将开始';

    protected $linkTitle = '参加直播';

    public function parse($options)
    {
        if (!ArrayToolkit::requireds($options, array('targetId', 'batch', 'url', 'title', 'cover', 'startTime'))) {
            throw new \InvalidArgumentException('Lack of required fields');
        }

        $this->linkUrl = isset($options['url']) ? $options['url'] : $this->getBaseUrl().'/';
        $imgUrl = $this->getFileUrl($options['cover'], 'course.png');

        $this->markdownContent = "#### 你的直播课程将开始  \n  ";
        $this->markdownContent .= "![]({$imgUrl})  \n  ";
        $this->markdownContent .= "课程名称：{$options['title']}  \n  ";
        $this->markdownContent .= '开始时间：'.date('Y-m-d H:i:s', $options['startTime'])."  \n  ";
        $this->markdownContent .= '###### 发送时间：'.date('Y-m-d H:i:s', time());

        return array(
            'targetType' => $this->type,
            'targetId' => $options['targetId'],
            'batch' => $options['batch'],
            'message' => $this->makeSingleContent(),
        );
    }
}
