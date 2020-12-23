<?php

namespace CorporateTrainingBundle\Biz\DingTalk\Template;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Exception\InvalidArgumentException;

class ClassroomAssignTemplate extends BaseTemplate implements DingTalkTemplateInterface
{
    protected $type = 'classroom_assign';

    protected $linkUrl = '';

    protected $markdownContent = '';

    protected $title = '你被指派新的专题进行学习';

    /**
     * {@inheritdoc}
     */
    public function parse($options)
    {
        if (!ArrayToolkit::requireds($options, array('targetId', 'imagePath', 'url', 'classroomTitle', 'batch'))) {
            throw new InvalidArgumentException('Lack of required fields');
        }
        $imgUrl = $this->getFileUrl($options['imagePath'], 'classroom.png');
        $this->linkUrl = isset($options['url']) ? $options['url'] : $this->getBaseUrl().'/';
        $this->markdownContent = "#### 你被指派新的专题进行学习  \n  ";
        $this->markdownContent .= "![]({$imgUrl})  \n  ";
        $this->markdownContent .= "专题名称：{$options['classroomTitle']}  \n  ";
        $this->markdownContent .= '###### 发送时间：'.date('Y-m-d H:i:s', time());

        return array(
            'targetType' => $this->type,
            'targetId' => $options['targetId'],
            'batch' => $options['batch'],
            'message' => $this->makeSingleContent(),
        );
    }
}
