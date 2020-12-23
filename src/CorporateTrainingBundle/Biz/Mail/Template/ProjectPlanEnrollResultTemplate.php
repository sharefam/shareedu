<?php

namespace CorporateTrainingBundle\Biz\Mail\Template;

use Biz\Mail\Template\EmailTemplateInterface;

class ProjectPlanEnrollResultTemplate extends BaseTemplate implements EmailTemplateInterface
{
    /**
     * {@inheritdoc}
     */
    public function parse($options)
    {
        return array(
            'title' => sprintf('项目报名成功', $this->getSiteName()),
            'body' => $this->renderBody('project-plan-enroll-result.txt.twig', $options['params']),
        );
    }
}
