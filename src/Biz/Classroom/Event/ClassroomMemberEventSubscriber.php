<?php

namespace Biz\Classroom\Event;

use Biz\Classroom\Service\ClassroomService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ClassroomMemberEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'course.task.finish' => 'onTaskFinish',
        );
    }

    public function onTaskFinish(Event $event)
    {
        $taskResult = $event->getSubject();
        $user = $event->getArgument('user');

        $classroomIds = $this->getClassroomService()->findClassroomIdsByCourseId($taskResult['courseId']);
        if (empty($classroomIds)) {
            return;
        }
        foreach ($classroomIds as $classroomId) {
            $this->getClassroomService()->updateLearndNumByClassroomIdAndUserId($classroomId['classroomId'], $user['id']);
        }
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->getBiz()->service('Classroom:ClassroomService');
    }
}
