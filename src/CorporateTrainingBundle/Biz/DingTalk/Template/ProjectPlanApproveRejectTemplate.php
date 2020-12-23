<?php

namespace CorporateTrainingBundle\Biz\DingTalk\Template;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Exception\InvalidArgumentException;

class ProjectPlanApproveRejectTemplate extends BaseTemplate implements DingTalkTemplateInterface
{
    protected $type = 'email_project_plan_enroll_reject';

    protected $linkUrl = '';

    protected $markdownContent = '';

    protected $title = '报名结果通知';

    /**
     * {@inheritdoc}
     */
    public function parse($options)
    {
        if (!ArrayToolkit::requireds($options, array('targetId', 'batch', 'url', 'imagePath', 'rejectedReason', 'projectPlanName'))) {
            throw new InvalidArgumentException('Lack of required fields');
        }
        $imgUrl = $this->getFileUrl($options['imagePath'], 'project-plan.png');
        $this->linkUrl = isset($options['url']) ? $options['url'] : $this->getBaseUrl().'/';
        $this->markdownContent = "#### 报名结果通知  \n  ";
        $this->markdownContent .= "你的报名未通过审核  \n  ";
        $this->markdownContent .= "原因：{$options['rejectedReason']}  \n  ";
        $this->markdownContent .= "![]({$imgUrl})  \n  ";
        $this->markdownContent .= "项目名称：{$options['projectPlanName']}  \n  ";
        $this->markdownContent .= '###### 发送时间：'.date('Y-m-d H:i:s', time());

        return array(
            'targetType' => $this->type,
            'targetId' => $options['targetId'],
            'batch' => $options['batch'],
            'message' => $this->makeSingleContent(),
        );
    }
}
