<?php

namespace CorporateTrainingBundle\Biz\Task\Event;

use Biz\Course\Service\CourseService;
use Biz\System\Service\SettingService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Event\EventSubscriber;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TaskEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'course.task.publish' => 'onCourseTaskUpdate',
            'course.task.update' => 'onCourseTaskUpdate',
            'course.task.unpublish' => 'onCourseTaskDelete',
            'course.task.delete' => 'onCourseTaskDelete',
        );
    }

    public function onCourseTaskUpdate(Event $event)
    {
        $task = $event->getSubject();

        if ('live' == $task['type']) {
            $this->registerNotificationJob($task);
        }
    }

    public function onCourseTaskDelete(Event $event)
    {
        $task = $event->getSubject();
        if ('live' == $task['type']) {
            $this->deleteNotificationJob($task);
        }
    }

    protected function registerNotificationJob($task)
    {
        $this->deleteNotificationJob($task);

        $mailNotification = $this->getSettingService()->get('mail_notification', array());

        if (!empty($mailNotification['live_course'])) {
            $job = array(
                'name' => 'LiveCourseStartMailNotificationJob_'.$task['id'],
                'source' => 'TRAININGMAIN',
                'expression' => ($task['startTime'] < time() || ($task['startTime'] - time()) < 24 * 60 * 60) ? time() : strtotime('-1 day', $task['startTime']),
                'class' => 'CorporateTrainingBundle\Biz\Course\Job\CourseMailNotificationJob',
                'args' => array(
                    'courseId' => $task['courseId'],
                    'taskId' => $task['id'],
                    'template' => 'live_course_start',
                ),
                'misfire_policy' => 'executing',
            );

            $this->getSchedulerService()->register($job);
        }

        $dingtalkNotification = $this->getSettingService()->get('dingtalk_notification', array());
        if (!empty($dingtalkNotification['live_course_start']) && ($task['startTime'] - time()) >= 60 * 60) {
            global $kernel;
            $job = array(
                'name' => 'LiveCourseStartDingtalkNotification_'.$task['id'],
                'source' => 'TRAININGMAIN',
                'expression' => $task['startTime'] - 60 * 60,
                'class' => 'CorporateTrainingBundle\Biz\Course\Job\LiveDingTalkNotificationJob',
                'args' => array(
                    'notificationType' => 'live_course_start',
                    'courseId' => $task['courseId'],
                    'taskId' => $task['id'],
                    'template' => 'live_course_start_remind',
                    'url' => $kernel->getContainer()->get('router')->generate('course_task_show', array('courseId' => $task['courseId'], 'id' => $task['id']), true),
                ),
                'misfire_policy' => 'executing',
            );

            $this->getSchedulerService()->register($job);
        }
    }

    protected function deleteNotificationJob($task)
    {
        $this->getSchedulerService()->deleteJobByName('LiveCourseStartMailNotificationJob_'.$task['id']);
        $this->getSchedulerService()->deleteJobByName('LiveCourseStartDingtalkNotification_'.$task['id']);
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    /**
     * @return SchedulerService
     */
    protected function getSchedulerService()
    {
        return $this->getBiz()->service('Scheduler:SchedulerService');
    }
}
