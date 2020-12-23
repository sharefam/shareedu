<?php

namespace CorporateTrainingBundle\Biz\Classroom\Event;

use Biz\System\Service\SettingService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ClassroomEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'classroom.course.create' => 'onClassroomCourseCreate',
        );
    }

    public function onClassroomCourseCreate(Event $event)
    {
        $classroom = $event->getSubject();
        $newCourseCount = $event->getArgument('newCourseCount');
        $members = $this->getClassroomService()->findClassroomStudents($classroom['id'], 0, 1);
        if (!empty($newCourseCount) && !empty($members)) {
            $classroom['newCourseCount'] = $newCourseCount;
            $this->sendNotification($classroom);
        }
    }

    protected function sendNotification($classroom)
    {
        $dingtalkSetting = $this->getSettingService()->get('dingtalk_notification', array());
        if (!empty($dingtalkSetting['classroom_course_update'])) {
            $types[] = 'dingtalk';
        }
        if (empty($types)) {
            return;
        }

        global $kernel;
        $url = $kernel->getContainer()->get('router')->generate('classroom_introductions', array('id' => $classroom['id']), true);
        $to = array(
            'type' => 'classroom',
            'classroomId' => $classroom['id'],
            'startNum' => 0,
            'perPageNum' => 20,
        );
        $content = array(
            'template' => 'classroom_course_update',
            'params' => array(
                'targetId' => $classroom['id'],
                'batch' => 'classroom_course_update'.$classroom['id'].time(),
                'classroomTitle' => $classroom['title'],
                'url' => $url,
                'imagePath' => $classroom['middlePicture'],
                'num' => $classroom['newCourseCount'],
            ),
        );

        $this->getBiz()->offsetGet('notification_default')->send($types, $to, $content);
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\Classroom\Service\ClassroomService
     */
    private function getClassroomService()
    {
        return $this->getBiz()->service('Classroom:ClassroomService');
    }
}
