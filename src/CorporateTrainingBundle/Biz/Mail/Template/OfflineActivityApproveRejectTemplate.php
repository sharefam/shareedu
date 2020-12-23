<?php

namespace CorporateTrainingBundle\Biz\Mail\Template;

use Biz\Mail\Template\EmailTemplateInterface;

class OfflineActivityApproveRejectTemplate extends BaseTemplate implements EmailTemplateInterface
{
    /**
     * {@inheritdoc}
     */
    public function parse($options)
    {
        return array(
            'title' => sprintf('活动报名失败', $this->getSiteName()),
            'body' => $this->renderBody('offline-activity-approve-reject.txt.twig', $options['params']),
        );
    }
}
