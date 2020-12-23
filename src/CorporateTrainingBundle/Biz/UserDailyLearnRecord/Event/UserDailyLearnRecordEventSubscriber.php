<?php

namespace CorporateTrainingBundle\Biz\UserDailyLearnRecord\Event;

use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use CorporateTrainingBundle\Biz\UserDailyLearnRecord\EventProcessor\EventProcessorFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserDailyLearnRecordEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'course.join' => 'onCourseJoin',
            'course.task.finish' => 'onTaskFinish',
            'course.view' => 'onCourseView',
            'task.view' => 'onTaskView',
            'wave.learn.time' => 'onWaveLearnTime',
        );
    }

    public function onCourseJoin(Event $event)
    {
        $course = $event->getSubject();
        $userId = $event->getArgument('userId');

        $user = $this->getUserService()->getUser($userId);

        if (!$this->validateData($user, $course)) {
            return;
        }

        $data = $this->buildData($user, $course);

        $this->processDailyRecord('course.join', $data);
    }

    public function onCourseView(Event $event)
    {
        $course = $event->getSubject();
        $userId = $event->getArgument('userId');

        $user = $this->getUserService()->getUser($userId);

        $member = $this->getCourseMemberService()->getCourseMember($course['id'], $userId);

        if (empty($member) || !$this->validateData($user, $course)) {
            return;
        }

        $data = $this->buildData($user, $course);

        $this->processDailyRecord('course.view', $data);
    }

    public function onTaskFinish(Event $event)
    {
        $taskResult = $event->getSubject();
        $user = $event->getArgument('user');

        $course = $this->getCourseService()->getCourse($taskResult['courseId']);

        if (!$this->validateData($user, $course)) {
            return;
        }

        $data = $this->buildData($user, $course);

        if ('serialized' !== $course['serializeMode']) {
            $learnedCompulsoryTaskNum = $this->getTaskResultService()->countFinishedCompulsoryTasksByUserIdAndCourseId($user['id'], $course['id']);

            if ($learnedCompulsoryTaskNum >= $course['compulsoryTaskNum']) {
                $data['courseStatus'] = 1;
            }
        }

        $this->processDailyRecord('task.finish', $data);
    }

    public function onTaskView(Event $event)
    {
        $courseMember = $event->getSubject();

        $user = $this->getUserService()->getUser($courseMember['userId']);
        $course = $this->getCourseService()->getCourse($courseMember['courseId']);

        $member = $this->getCourseMemberService()->getCourseMember($course['id'], $courseMember['userId']);

        if (empty($member) || !$this->validateData($user, $course)) {
            return;
        }

        if (empty($courseMember)) {
            return;
        }

        $data = $this->buildData($user, $course);

        $this->processDailyRecord('task.view', $data);
    }

    public function onWaveLearnTime(Event $event)
    {
        $taskResultId = $event->getSubject();
        $learnTime = $event->getArgument('learnTime');

        $taskResult = $this->getTaskResultService()->getTaskResultById($taskResultId);

        $user = $this->getUserService()->getUser($taskResult['userId']);

        $courseId = $taskResult['courseId'];
        $course = $this->getCourseService()->getCourse($courseId);

        if (!$this->validateData($user, $course)) {
            return;
        }

        $data = $this->buildData($user, $course);
        $data['learnTime'] = $learnTime;

        $this->processDailyRecord('wave.learn.time', $data);
    }

    protected function validateData($user, $course)
    {
        if (empty($user) || empty($course) || !empty($user['locked'])) {
            return false;
        }

        if (in_array($user['id'], $course['teacherIds'])) {
            return false;
        }

        return true;
    }

    protected function buildData($user, $course)
    {
        $data = array(
            'courseId' => $course['id'],
            'categoryId' => $course['categoryId'],
            'userId' => $user['id'],
            'postId' => $user['postId'],
        );

        return $data;
    }

    protected function processDailyRecord($type, $data)
    {
        return EventProcessorFactory::create($type)->process($data);
    }

    /**
     * @return \Biz\User\Service\Impl\UserServiceImpl
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return \Biz\Course\Service\Impl\CourseServiceImpl
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return \Biz\Task\Service\Impl\TaskResultServiceImpl
     */
    protected function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }

    /**
     * @return \Biz\Classroom\Service\Impl\ClassroomServiceImpl
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    protected function createService($alias)
    {
        return $this->getBiz()->service($alias);
    }
}
