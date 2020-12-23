<?php

namespace CorporateTrainingBundle\Biz\Mail\Template;

use Biz\Mail\Template\EmailTemplateInterface;

class OfflineExamStartTemplate extends BaseTemplate implements EmailTemplateInterface
{
    /**
     * {@inheritdoc}
     */
    public function parse($options)
    {
        return array(
            'title' => sprintf('考试通知', $this->getSiteName()),
            'body' => $this->renderBody('project-plan-offline-exam-start.txt.twig', $options['params']),
        );
    }
}
