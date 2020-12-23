<?php

namespace CorporateTrainingBundle\Biz\Mail\Template;

use Biz\Mail\Template\EmailTemplateInterface;

class LiveCourseTemplate extends BaseTemplate implements EmailTemplateInterface
{
    /**
     * {@inheritdoc}
     */
    public function parse($options)
    {
        return array(
            'title' => sprintf('直播课程通知', $this->getSiteName()),
            'body' => $this->renderBody('live-course-start.txt.twig', $options['params']),
        );
    }
}
