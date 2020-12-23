<?php

namespace CorporateTrainingBundle\Biz\Mail\Template;

use Biz\Mail\Template\EmailTemplateInterface;

class ExamResultTemplate extends BaseTemplate implements EmailTemplateInterface
{
    /**
     * {@inheritdoc}
     */
    public function parse($options)
    {
        return array(
            'title' => sprintf('考试结果', $this->getSiteName()),
            'body' => $this->renderBody('exam-result.txt.twig', $options['params']),
        );
    }
}
