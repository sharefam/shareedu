<?php

namespace CorporateTrainingBundle\Biz\Mail\Template;

use Biz\Mail\Template\EmailTemplateInterface;

class OfflineCourseQuestionnaireTemplate extends BaseTemplate implements EmailTemplateInterface
{
    /**
     * {@inheritdoc}
     */
    public function parse($options)
    {
        return array(
            'title' => sprintf('教评问卷填写通知', $this->getSiteName()),
            'body' => $this->renderBody('offline-course-questionnaire.txt.twig', $options['params']),
        );
    }
}
