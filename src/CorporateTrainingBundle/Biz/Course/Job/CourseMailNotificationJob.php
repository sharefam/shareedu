<?php

namespace CorporateTrainingBundle\Biz\Course\Job;

use Biz\System\Service\LogService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;
use Codeages\Biz\Framework\Queue\Service\QueueService;
use CorporateTrainingBundle\Biz\Course\Service\CourseService;
use CorporateTrainingBundle\Biz\Course\Service\CourseSetService;
use CorporateTrainingBundle\Biz\Task\Service\TaskService;

class CourseMailNotificationJob extends AbstractJob
{
    public function execute()
    {
        $course = $this->getCourseService()->getCourse($this->args['courseId']);
        $task = $this->getTaskService()->getTask($this->args['taskId']);

        $to = array(
            'type' => 'course',
            'courseId' => $this->args['courseId'],
            'startNum' => 0,
            'perPageNum' => 20,
        );
        $content = array(
            'template' => $this->args['template'],
            'params' => array(
                'courseTitle' => $course['title'],
                'taskTitle' => $task['title'],
                'startTime' => date('Y-m-d H:i:s', $task['startTime']),
            ),
        );

        if ($this->canSend()) {
            $this->biz->offsetGet('notification_email')->send($to, $content);
        }
    }

    protected function canSend()
    {
        $task = $this->getTaskService()->getTask($this->args['taskId']);

        if (empty($task) || 'published' != $task['status']) {
            return false;
        }

        return true;
    }

    /**
     * @return QueueService
     */
    protected function getQueueService()
    {
        return $this->biz->service('Queue:QueueService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->biz->service('System:LogService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->biz->service('CorporateTrainingBundle:Course:CourseService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->biz->service('CorporateTrainingBundle:Task:TaskService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->biz->service('CorporateTrainingBundle:Course:CourseSetService');
    }
}
