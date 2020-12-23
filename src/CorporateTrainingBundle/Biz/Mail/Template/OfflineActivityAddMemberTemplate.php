<?php

namespace CorporateTrainingBundle\Biz\Mail\Template;

use Biz\Mail\Template\EmailTemplateInterface;

class OfflineActivityAddMemberTemplate extends BaseTemplate implements EmailTemplateInterface
{
    /**
     * {@inheritdoc}
     */
    public function parse($options)
    {
        return array(
            'title' => sprintf('活动报名成功', $this->getSiteName()),
            'body' => $this->renderBody('offline-activity-add-member.txt.twig', $options['params']),
        );
    }
}
