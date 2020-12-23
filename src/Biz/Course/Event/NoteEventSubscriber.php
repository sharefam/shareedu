<?php

namespace Biz\Course\Event;

use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Codeages\Biz\Framework\Event\Event;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\CourseNoteService;
use Biz\Classroom\Service\ClassroomService;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NoteEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'course.note.create' => 'onCourseNoteCreate',
            'course.note.update' => 'onCourseNoteUpdate',
            'course.note.delete' => 'onCourseNoteDelete',
            'course.note.liked' => 'onCourseNoteLike',
            'course.note.cancelLike' => 'onCourseNoteCancelLike',
        );
    }

    public function onCourseNoteCreate(Event $event)
    {
        $note = $event->getSubject();
        $classrooms = $this->getClassroomService()->findClassroomIdsByCourseId($note['courseId']);

        if (!empty($classrooms) && CourseNoteService::PUBLIC_STATUS == $note['status']) {
            foreach ($classrooms as $classroom) {
                $this->getClassroomService()->waveClassroom($classroom['classroomId'], 'noteNum', +1);
            }
        }

        $this->getCourseMemberService()->refreshMemberNoteNumber($note['courseId'], $note['userId']);
        $this->getCourseService()->updateCourseStatistics($note['courseId'], array('noteNum'));
        $this->getCourseSetService()->updateCourseSetStatistics($note['courseSetId'], array('noteNum'));
    }

    public function onCourseNoteUpdate(Event $event)
    {
        $note = $event->getSubject();
        $this->getCourseService()->updateCourseStatistics($note['courseId'], array('noteNum'));
        $this->getCourseSetService()->updateCourseSetStatistics($note['courseSetId'], array('noteNum'));
        $this->getCourseMemberService()->refreshMemberNoteNumber($note['courseId'], $note['userId']);

        $classrooms = $this->getClassroomService()->findClassroomIdsByCourseId($note['courseId']);

        if (empty($classrooms)) {
            return;
        }

        $preStatus = $event->getArgument('preStatus');

        if (CourseNoteService::PUBLIC_STATUS == $note['status'] && CourseNoteService::PRIVATE_STATUS == $preStatus) {
            foreach ($classrooms as $classroom) {
                $this->getClassroomService()->waveClassroom($classroom['classroomId'], 'noteNum', +1);
            }
        }

        if (CourseNoteService::PRIVATE_STATUS == $note['status'] && CourseNoteService::PUBLIC_STATUS == $preStatus) {
            foreach ($classrooms as $classroom) {
                $this->getClassroomService()->waveClassroom($classroom['classroomId'], 'noteNum', -1);
            }
        }
    }

    public function onCourseNoteDelete(Event $event)
    {
        $note = $event->getSubject();

        $classrooms = $this->getClassroomService()->findClassroomIdsByCourseId($note['courseId']);

        if (!empty($classrooms)) {
            foreach ($classrooms as $classroom) {
                $this->getClassroomService()->waveClassroom($classroom['classroomId'], 'noteNum', -1);
            }
        }

        $this->getCourseService()->updateCourseStatistics($note['courseId'], array('noteNum'));
        $this->getCourseSetService()->updateCourseSetStatistics($note['courseSetId'], array('noteNum'));
        $this->getCourseMemberService()->refreshMemberNoteNumber($note['courseId'], $note['userId']);
    }

    public function onCourseNoteLike(Event $event)
    {
        $note = $event->getSubject();
        $this->getCourseNoteService()->waveLikeNum($note['id'], +1);
    }

    public function onCourseNoteCancelLike(Event $event)
    {
        $note = $event->getSubject();
        $this->getCourseNoteService()->waveLikeNum($note['id'], -1);
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    /**
     * @return CourseNoteService
     */
    protected function getCourseNoteService()
    {
        return $this->getBiz()->service('Course:CourseNoteService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->getBiz()->service('Course:MemberService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->getBiz()->service('Classroom:ClassroomService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }
}
