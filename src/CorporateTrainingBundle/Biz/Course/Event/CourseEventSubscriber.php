<?php

namespace CorporateTrainingBundle\Biz\Course\Event;

use AppBundle\Common\ArrayToolkit;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Dao\CourseDao;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CourseEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'course-set.update' => 'onCourseSetUpdate',
            'course.teachers.update' => array('onCourseTeacherUpdate', 255),
        );
    }

    public function onCourseSetUpdate(Event $event)
    {
        $courseSet = $event->getSubject();

        if (empty($courseSet['defaultCourseId'])) {
            return;
        }

        $copyFields = ArrayToolkit::parts(
            $courseSet,
            array(
                'title',
                'categoryId',
                'summary',
                'goals',
                'audiences',
                'serializeMode',
            )
        );

        $this->getCourseDao()->update($courseSet['defaultCourseId'], $copyFields);
    }

    public function onCourseTeacherUpdate(Event $event)
    {
        $course = $event->getSubject();
        if (empty($course)) {
            return;
        }
        $classrooms = $this->getClassroomService()->findClassroomIdsByCourseId($course['id']);
        if (empty($classrooms)) {
            return;
        }
        foreach ($classrooms as $classroom) {
            $this->getClassroomService()->updateClassroomTeachers($classroom['classroomId']);
        }
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->getBiz()->service('Classroom:ClassroomService');
    }

    /**
     * @return CourseDao
     */
    protected function getCourseDao()
    {
        return $this->getBiz()->dao('Course:CourseDao');
    }
}
