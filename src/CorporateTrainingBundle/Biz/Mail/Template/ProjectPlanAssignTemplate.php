<?php

namespace CorporateTrainingBundle\Biz\Mail\Template;

use Biz\Mail\Template\EmailTemplateInterface;

class ProjectPlanAssignTemplate extends BaseTemplate implements EmailTemplateInterface
{
    /**
     * {@inheritdoc}
     */
    public function parse($options)
    {
        return array(
            'title' => sprintf('培训项目', $this->getSiteName()),
            'body' => $this->renderBody('project-plan-assign.txt.twig', $options['params']),
        );
    }
}
