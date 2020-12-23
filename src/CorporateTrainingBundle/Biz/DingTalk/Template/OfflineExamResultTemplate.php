<?php

namespace CorporateTrainingBundle\Biz\DingTalk\Template;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Exception\InvalidArgumentException;

class OfflineExamResultTemplate extends BaseTemplate implements DingTalkTemplateInterface
{
    protected $type = 'project_plan_offline_exam_result';

    protected $title = '考试结果通知';

    protected $linkTitle = '查看详情';

    public function parse($options)
    {
        if (!ArrayToolkit::requireds($options, array('targetId', 'batch', 'url', 'examName', 'score', 'totalScore', 'status'))) {
            throw new InvalidArgumentException('Lack of required fields');
        }
        $imgUrl = $this->getBaseUrl().'/assets/img/dingtalk/exam.png';
        $this->linkUrl = isset($options['url']) ? $options['url'] : $this->getBaseUrl().'/';
        $this->markdownContent = "#### 考试结果通知  \n  ";
        $this->markdownContent .= "![]({$imgUrl})  \n  ";
        $this->markdownContent .= "考试名称：{$options['examName']}  \n  ";
        $this->markdownContent .= "考试成绩：{$options['score']}/{$options['totalScore']}  \n  ";
        $this->markdownContent .= "考试结果：{$options['status']}  \n  ";
        $this->markdownContent .= '###### 发送时间：'.date('Y-m-d H:i:s', time());

        return array(
            'targetType' => $this->type,
            'targetId' => $options['targetId'],
            'batch' => $options['batch'],
            'message' => $this->makeSingleContent(),
        );
    }
}
