<?php

namespace CorporateTrainingBundle\Biz\Mail\Template;

use Biz\Mail\Template\EmailTemplateInterface;

class OfflineCourseTemplate extends BaseTemplate implements EmailTemplateInterface
{
    /**
     * {@inheritdoc}
     */
    public function parse($options)
    {
        return array(
            'title' => sprintf('线下课程通知', $this->getSiteName()),
            'body' => $this->renderBody('offline-course-task.txt.twig', $options['params']),
        );
    }
}
