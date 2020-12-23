<?php

namespace CorporateTrainingBundle\Biz\ResourceScope\Strategy;

use Biz\User\Service\UserService;

abstract class BaseStrategy implements ResourceScopeStrategy
{
    const EMPTY_SCOPE_VALUE = 0;
    protected $biz;

    public function __construct($biz)
    {
        $this->biz = $biz;
    }

    protected function getCurrentUser()
    {
        return $this->biz['user'];
    }

    protected function getLogger()
    {
        return $this->biz['logger'];
    }

    protected function beginTransaction()
    {
        $this->biz['db']->beginTransaction();
    }

    protected function commit()
    {
        $this->biz['db']->commit();
    }

    protected function rollback()
    {
        $this->biz['db']->rollback();
    }

    protected function createService($alias)
    {
        return $this->biz->service($alias);
    }

    protected function createDao($alias)
    {
        return $this->biz->Dao($alias);
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
