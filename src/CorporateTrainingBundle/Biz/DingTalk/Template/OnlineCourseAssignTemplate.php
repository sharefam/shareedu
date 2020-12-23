<?php

namespace CorporateTrainingBundle\Biz\DingTalk\Template;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Exception\InvalidArgumentException;

class OnlineCourseAssignTemplate extends BaseTemplate implements DingTalkTemplateInterface
{
    protected $type = 'online_course_assign';

    protected $linkUrl = '';

    protected $markdownContent = '';

    protected $title = '你被指派新的课程进行学习';

    /**
     * {@inheritdoc}
     */
    public function parse($options)
    {
        if (!ArrayToolkit::requireds($options, array('targetId', 'batch', 'url', 'imagePath', 'courseTitle'))) {
            throw new InvalidArgumentException('Lack of required fields');
        }
        $imgUrl = $this->getFileUrl($options['imagePath'], 'course.png');
        $this->linkUrl = isset($options['url']) ? $options['url'] : $this->getBaseUrl().'/';
        $this->markdownContent = "#### 你被指派新的课程进行学习  \n  ";
        $this->markdownContent .= "![]({$imgUrl})  \n  ";
        $this->markdownContent .= "课程名称：{$options['courseTitle']}  \n  ";
        $this->markdownContent .= '###### 发送时间：'.date('Y-m-d H:i:s', time());

        return array(
            'targetType' => $this->type,
            'targetId' => $options['targetId'],
            'batch' => $options['batch'],
            'message' => $this->makeSingleContent(),
        );
    }
}
