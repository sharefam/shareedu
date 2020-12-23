<?php

namespace CorporateTrainingBundle\Biz\AdvancedMemberSelect;

use AppBundle\Common\Exception\InvalidArgumentException;
use Codeages\Biz\Framework\Context\BizAware;

class MemberSelectFactory extends BizAware
{
    public function create($exportType)
    {
        $exportType = 'advanced_member_select.'.$exportType;
        if (!isset($this->biz[$exportType])) {
            throw new InvalidArgumentException(sprintf('Unknown member select type %s', $exportType));
        }

        return $this->biz[$exportType];
    }
}
