<?php

namespace CorporateTrainingBundle\Biz\Mail\Template;

use Biz\Mail\Template\EmailTemplateInterface;

class ProjectPlanUpdateTemplate extends BaseTemplate implements EmailTemplateInterface
{
    public function parse($options)
    {
        return array(
            'title' => sprintf('培训项目更新', $this->getSiteName()),
            'body' => $this->renderBody('project-plan-update.txt.twig', $options['params']),
        );
    }
}
