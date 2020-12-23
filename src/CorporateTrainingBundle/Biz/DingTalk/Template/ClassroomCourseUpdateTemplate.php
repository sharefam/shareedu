<?php

namespace CorporateTrainingBundle\Biz\DingTalk\Template;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Exception\InvalidArgumentException;

class ClassroomCourseUpdateTemplate extends BaseTemplate implements DingTalkTemplateInterface
{
    protected $type = 'classroom_course_update';

    protected $linkUrl = '';

    protected $markdownContent = '';

    protected $title = '专题课程更新通知';

    /**
     * {@inheritdoc}
     */
    public function parse($options)
    {
        if (!ArrayToolkit::requireds($options, array('targetId', 'imagePath', 'url', 'classroomTitle', 'batch', 'num'))) {
            throw new InvalidArgumentException('Lack of required fields');
        }
        $num = isset($options['num']) ? $options['num'] : 0;
        $imgUrl = $this->getFileUrl($options['imagePath'], 'classroom.png');
        $this->linkUrl = isset($options['url']) ? $options['url'] : $this->getBaseUrl().'/';
        $this->markdownContent = "#### 专题课程更新通知  \n  ";
        $this->markdownContent .= "![]({$imgUrl})  \n  ";
        $this->markdownContent .= "你的专题{$options['classroomTitle']}更新了{$num}门新课程  \n  ";
        $this->markdownContent .= '###### 发送时间：'.date('Y-m-d H:i:s', time());

        return array(
            'targetType' => $this->type,
            'targetId' => $options['targetId'],
            'batch' => $options['batch'],
            'message' => $this->makeSingleContent(),
        );
    }
}
