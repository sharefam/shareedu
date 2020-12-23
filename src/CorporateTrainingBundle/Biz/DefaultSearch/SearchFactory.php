<?php

namespace CorporateTrainingBundle\Biz\DefaultSearch;

use AppBundle\Common\Exception\InvalidArgumentException;
use Codeages\Biz\Framework\Context\BizAware;

class SearchFactory extends BizAware
{
    public function create($type)
    {
        $type = $type.'Search';
        if (!isset($this->biz[$type])) {
            throw new InvalidArgumentException(sprintf('Unknown search type %s', $type));
        }

        return $this->biz[$type];
    }
}
