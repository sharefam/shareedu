<?php

namespace CorporateTrainingBundle\Biz\DiscoveryColumn;

use AppBundle\Common\Exception\InvalidArgumentException;
use Codeages\Biz\Framework\Context\BizAware;

class DiscoveryColumnFactory extends BizAware
{
    public function create($type)
    {
        $columnType = 'column.'.$type;
        if (!isset($this->biz[$columnType])) {
            throw new InvalidArgumentException(sprintf('Unknown export type %s', $columnType));
        }

        return $this->biz[$columnType];
    }
}
