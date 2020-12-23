<?php

namespace CorporateTrainingBundle\Biz\Course\Job;

use CorporateTrainingBundle\Biz\Course\Service\CourseService;
use CorporateTrainingBundle\Biz\Course\Service\CourseSetService;
use CorporateTrainingBundle\Biz\DingTalk\Job\AbstractDingTalkNotificationJob;
use CorporateTrainingBundle\Biz\Task\Service\TaskService;

class LiveDingTalkNotificationJob extends AbstractDingTalkNotificationJob
{
    protected function canSend()
    {
        $task = $this->getTaskService()->getTask($this->args['taskId']);

        if (empty($task) || 'published' != $task['status']) {
            return false;
        }

        return true;
    }

    protected function buildNotificationData()
    {
        $course = $this->getCourseService()->getCourse($this->args['courseId']);
        $task = $this->getTaskService()->getTask($this->args['taskId']);
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);

        $to = array(
            'type' => 'course',
            'courseId' => $course['id'],
            'startNum' => 0,
            'perPageNum' => 20,
        );
        $content = array(
            'template' => $this->args['template'],
            'params' => array(
                'targetId' => $course['id'],
                'batch' => 'live_course_start'.$course['id'].time(),
                'url' => $this->args['url'],
                'title' => $course['title'],
                'cover' => empty($courseSet['cover']['large']) ? '' : $courseSet['cover']['large'],
                'startTime' => $task['startTime'],
            ),
        );

        return array($to, $content);
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
