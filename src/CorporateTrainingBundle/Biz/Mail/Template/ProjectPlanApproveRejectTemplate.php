<?php

namespace CorporateTrainingBundle\Biz\Mail\Template;

use Biz\Mail\Template\EmailTemplateInterface;

class ProjectPlanApproveRejectTemplate extends BaseTemplate implements EmailTemplateInterface
{
    /**
     * {@inheritdoc}
     */
    public function parse($options)
    {
        return array(
            'title' => sprintf('项目报名失败', $this->getSiteName()),
            'body' => $this->renderBody('project-plan-approve-reject.txt.twig', $options['params']),
        );
    }
}
