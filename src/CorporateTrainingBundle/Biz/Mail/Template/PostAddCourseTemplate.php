<?php

namespace CorporateTrainingBundle\Biz\Mail\Template;

use Biz\Mail\Template\EmailTemplateInterface;

class PostAddCourseTemplate extends BaseTemplate implements EmailTemplateInterface
{
    /**
     * {@inheritdoc}
     */
    public function parse($options)
    {
        return array(
            'title' => sprintf('岗位课程更新', $this->getSiteName()),
            'body' => $this->renderBody('post-course-add.txt.twig', $options['params']),
        );
    }
}
