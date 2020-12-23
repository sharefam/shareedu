<?php

namespace CorporateTrainingBundle\Biz\Attachment;

use AppBundle\Common\Exception\InvalidArgumentException;
use Codeages\Biz\Framework\Context\BizAware;

class AttachmentFactory extends BizAware
{
    public function create($attachmentType)
    {
        $attachmentType = 'attachment.permission.'.$attachmentType;
        if (!isset($this->biz[$attachmentType])) {
            throw new InvalidArgumentException(sprintf('Unknown attachment type %s', $attachmentType));
        }

        return $this->biz[$attachmentType];
    }
}
