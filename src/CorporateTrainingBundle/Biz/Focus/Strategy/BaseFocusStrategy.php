<?php

namespace CorporateTrainingBundle\Biz\Focus\Strategy;

use Codeages\Biz\Framework\Context\Biz;

class BaseFocusStrategy
{
    protected $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    protected function getCurrentUser()
    {
        return $this->biz->offsetGet('user');
    }

    protected function createService($alias)
    {
        return $this->biz->service($alias);
    }

    protected function createDao($alias)
    {
        return $this->biz->dao($alias);
    }
}
