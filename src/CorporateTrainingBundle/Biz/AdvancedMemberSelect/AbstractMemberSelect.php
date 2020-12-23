<?php

namespace CorporateTrainingBundle\Biz\AdvancedMemberSelect;

use AppBundle\Common\Exception\AccessDeniedException;
use Biz\System\Service\SettingService;
use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Event\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

abstract class AbstractMemberSelect
{
    protected $biz;

    protected $resourceType = '';

    protected $mailNotification = array();

    protected $dingtalkNotification = array();

    abstract public function canSelect($targetId);

    abstract public function becomeMember($targetId, $userIds);

    abstract protected function sendNotification($userIds, $targetId);

    /**
     * @param $targetId
     * @param $userIds
     *
     * @return mixed
     *
     * 过滤已经存在的用户
     */
    abstract protected function filterMembers($targetId, $userIds);

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
        $this->mailNotification = $this->getSettingService()->get('mail_notification', array());
        $this->dingtalkNotification = $this->getSettingService()->get('dingtalk_notification', array());
    }

    public function selectUserIds($targetId, $userIds, $notificationSetting = 0)
    {
        if (!$this->canSelect($targetId)) {
            throw new AccessDeniedException('Access Denied');
        }

        if (empty($targetId) || empty($userIds)) {
            return false;
        }

        if (!is_array($userIds)) {
            $userIds = explode(',', $userIds);
        }

        $userIds = $this->filterMembers($targetId, $userIds);
        $userIds = array_values($userIds);

        if (!empty($userIds)) {
            $this->becomeMember($targetId, $userIds);
        }

        if (!empty($userIds) && $notificationSetting) {
            $this->sendNotification($userIds, $targetId);
        }

        return true;
    }

    /**
     * @return EventDispatcherInterface
     */
    private function getDispatcher()
    {
        return $this->biz['dispatcher'];
    }

    /**
     * @param string      $eventName
     * @param Event|mixed $subject
     *
     * @return Event
     */
    protected function dispatchEvent($eventName, $subject, $arguments = array())
    {
        if ($subject instanceof Event) {
            $event = $subject;
        } else {
            $event = new Event($subject, $arguments);
        }

        return $this->getDispatcher()->dispatch($eventName, $event);
    }

    protected function createService($alias)
    {
        return $this->biz->service($alias);
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }
}
