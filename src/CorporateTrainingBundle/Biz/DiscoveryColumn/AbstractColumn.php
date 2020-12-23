<?php

namespace CorporateTrainingBundle\Biz\DiscoveryColumn;

use Codeages\Biz\Framework\Context\Biz;

abstract class AbstractColumn
{
    protected $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    abstract protected function buildColumn($parameters);
}
