<?php

namespace CorporateTrainingBundle\Biz\NotificationCenter\Strategy\Group;

use Biz\System\Service\LogService;
use Biz\System\Service\SettingService;
use Biz\User\Service\UserService;
use Codeages\Biz\Framework\Context\Biz;

class GroupStrategy
{
    protected $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    /**
     * @return \CorporateTrainingBundle\Biz\User\Service\UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    protected function createService($alias)
    {
        return $this->biz->service($alias);
    }

    protected function createDao($alias)
    {
        return $this->biz->service($alias);
    }
}
