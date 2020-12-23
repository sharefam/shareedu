<?php

namespace CorporateTrainingBundle\Biz\DingTalk\Template;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Exception\InvalidArgumentException;

class PostCourseAddTemplate extends BaseTemplate implements DingTalkTemplateInterface
{
    protected $type = 'post_course_add';

    protected $linkUrl = '';

    protected $markdownContent = '';

    protected $title = '岗位课程更新通知';

    /**
     * {@inheritdoc}
     */
    public function parse($options)
    {
        if (!ArrayToolkit::requireds($options, array('targetId', 'batch', 'url', 'num'))) {
            throw new InvalidArgumentException('Lack of required fields');
        }
        $num = isset($options['num']) ? $options['num'] : 0;
        $imgUrl = $this->getBaseUrl().'/assets/img/dingtalk/post.png';
        $this->linkUrl = isset($options['url']) ? $options['url'] : $this->getBaseUrl().'/';
        $this->markdownContent = "#### 岗位课程更新通知  \n  ";
        $this->markdownContent .= "![]({$imgUrl})  \n  ";
        $this->markdownContent .= "你的岗位课程更新了{$num}门新课程  \n  ";
        $this->markdownContent .= '###### 发送时间：'.date('Y-m-d H:i:s', time());

        return array(
            'targetType' => $this->type,
            'targetId' => $options['targetId'],
            'batch' => $options['batch'],
            'message' => $this->makeSingleContent(),
        );
    }
}
