<?php

namespace Biz\Announcement\Processor;

use Codeages\Biz\Framework\Context\Biz;

abstract class AnnouncementProcessor
{
    /**
     * @var Biz
     */
    protected $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    public function getUser()
    {
        $biz = $this->biz;

        return $biz['user'];
    }

    abstract public function checkManage($targetId);

    abstract public function checkTake($targetId);

    abstract public function getTargetShowUrl();

    abstract public function announcementNotification($targetId, $targetObject, $targetObjectShowUrl);

    abstract public function tryManageObject($targetId);

    abstract public function getTargetObject($targetId);

    abstract public function getActions($action);
}
