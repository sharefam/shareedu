<?php

namespace Biz\IM\Event;

use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ConversationEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'course.delete' => 'onCourseDelete',
            'classroom.delete' => 'onClassroomDelete',
            'org.create' => 'OrgCreate',
            'org.delete' => 'OrgDelete',
        );
    }

    public function OrgCreate(Event $event)
    {
        $org = $event->getSubject();
        $user = $this->getCurrentUser();

        if ($this->isIMEnabled()) {
            $this->getConversationService()->createConversation('部门会话', 'global', $org['id'], array($user));
        }

        return true;
    }

    public function OrgDelete(Event $event)
    {
        $org = $event->getSubject();

        if ($this->isIMEnabled()) {
            $this->getConversationService()->deleteConversationByTargetIdAndTargetType($org['id'], 'global');
            $this->getConversationService()->deleteMembersByTargetIdAndTargetType($org['id'], 'global');
        }

        return true;
    }

    public function isIMEnabled()
    {
        $setting = $this->getSettingService()->get('app_im', array());

        if (empty($setting) || empty($setting['enabled'])) {
            return false;
        }

        return true;
    }

    protected function getCurrentUser()
    {
        $biz = $this->getBiz();

        return $biz['user'];
    }

    public function onCourseDelete(Event $event)
    {
        $course = $event->getSubject();

        $this->getConversationService()->deleteConversationByTargetIdAndTargetType($course['id'], 'course');
        $this->getConversationService()->deleteMembersByTargetIdAndTargetType($course['id'], 'course');

        return true;
    }

    public function onClassroomDelete(Event $event)
    {
        $classroom = $event->getSubject();

        $this->getConversationService()->deleteConversationByTargetIdAndTargetType($classroom['id'], 'classroom');
        $this->getConversationService()->deleteMembersByTargetIdAndTargetType($classroom['id'], 'classroom');
    }

    protected function getClassroomService()
    {
        return $this->getBiz()->service('Classroom:ClassroomService');
    }

    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    /**
     * @return \Biz\IM\Service\Impl\ConversationServiceImpl
     */
    protected function getConversationService()
    {
        return $this->getBiz()->service('IM:ConversationService');
    }

    /**
     * @return \Biz\System\Service\Impl\SettingServiceImpl
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }
}
