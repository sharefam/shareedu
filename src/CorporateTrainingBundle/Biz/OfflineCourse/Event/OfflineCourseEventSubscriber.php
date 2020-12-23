<?php

namespace CorporateTrainingBundle\Biz\OfflineCourse\Event;

use AppBundle\Common\ArrayToolkit;
use Biz\System\Service\SettingService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OfflineCourseEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'offline_course.task.create' => 'onOfflineCourseCreateTask',
            'offline_course.task.update' => 'onOfflineCourseUpdateTask',
            'offline_course.task.delete' => 'onOfflineCourseDeleteTask',
            'offline_course.teacher.set' => 'onOfflineCourseSetTeacher',
            'project.plan.offline.course.attended' => 'onOfflineCourseAttended',
            'project.plan.offline.course.signin' => 'onOfflineCourseSignin',
        );
    }

    public function onOfflineCourseAttended(Event $event)
    {
        $taskResult = $event->getSubject();
        if ('attended' == $taskResult['attendStatus'] && $this->isFinishAllOfflineTask($taskResult) && !$this->isFinishAllQuestionnaire($taskResult)) {
            $this->sendAttendedNotificationEmail($taskResult);
        }
    }

    public function onOfflineCourseSignin(Event $event)
    {
        $taskResult = $event->getSubject();
        if ('attended' == $taskResult['attendStatus'] && $this->isFinishAllOfflineTask($taskResult) && !$this->isFinishAllQuestionnaire($taskResult)) {
            $this->sendAttendedNotificationEmail($taskResult);
        }
    }

    protected function sendAttendedNotificationEmail($taskResult)
    {
        $offlineCourse = $this->getOfflineCourseService()->getOfflineCourse($taskResult['offlineCourseId']);

        global $kernel;
        $url = $kernel->getContainer()->get('router')->generate('study_center_my_task_training_list', array(), true);

        $to = array(
            'type' => 'user',
            'userIds' => array($taskResult['userId']),
            'startNum' => 0,
            'perPageNum' => 20,
        );

        $content = array(
            'template' => 'offline_course_questionnaire',
            'params' => array(
                'offlineCourseTitle' => $offlineCourse['title'],
                'url' => $url,
            ),
        );

        $this->getBiz()->offsetGet('notification_email')->send($to, $content);
    }

    protected function isFinishAllOfflineTask($taskResult)
    {
        $result = false;
        $tasks = $this->getOfflineCourseTaskService()->searchTasks(
            array('offlineCourseId' => $taskResult['offlineCourseId'], 'type' => 'offlineCourse'),
            array('id' => 'ASC'),
            0,
            PHP_INT_MAX
        );

        if (empty($tasks)) {
            return $result;
        }

        $taskIds = ArrayToolkit::column($tasks, 'id');
        $finishTaskResults = $this->getOfflineCourseTaskService()->searchTaskResults(
            array('taskIds' => $taskIds, 'status' => 'finish', 'userId' => $taskResult['userId']),
            array('id' => 'ASC'),
            0,
            PHP_INT_MAX
        );

        if ((count($taskIds) - 1) <= count($finishTaskResults)) {
            $result = true;
        }

        return $result;
    }

    protected function isFinishAllQuestionnaire($taskResult)
    {
        $result = false;
        $questionnaires = $this->getOfflineCourseTaskService()->searchTasks(
            array('offlineCourseId' => $taskResult['offlineCourseId'], 'type' => 'questionnaire'),
            array('id' => 'ASC'),
            0,
            PHP_INT_MAX
        );

        if (empty($questionnaires)) {
            return true;
        }

        $taskIds = ArrayToolkit::column($questionnaires, 'id');
        $finishQuestionnaires = $this->getOfflineCourseTaskService()->searchTaskResults(
            array('taskIds' => $taskIds, 'status' => 'finish', 'userId' => $taskResult['userId']),
            array('id' => 'ASC'),
            0,
            PHP_INT_MAX
        );

        if (count($taskIds) <= count($finishQuestionnaires)) {
            $result = true;
        }

        return $result;
    }

    public function onOfflineCourseCreateTask(Event $event)
    {
        $task = $event->getSubject();
        $this->changeTaskNum($task);
        $this->changeTime($task);

        if ('offlineCourse' == $task['type']) {
            $this->sendEmailNotification($task);
        }
    }

    protected function sendEmailNotification($task)
    {
        $course = $this->getOfflineCourseService()->getOfflineCourse($task['offlineCourseId']);
        $jobName = 'OfflineCourseMailNotificationJob';

        $mailNotification = $this->getSettingService()->get('mail_notification', array());

        if (empty($mailNotification['project_plan_progress'])) {
            return;
        }

        if (!empty($course['projectPlanId'])) {
            if ($task['startTime'] < time() || ($task['startTime'] - time()) < 24 * 60 * 60) {
                $executeTime = time();
            } else {
                $executeTime = strtotime('-1 day', $task['startTime']);
            }

            $job = array(
                'name' => $jobName,
                'source' => 'TRAININGMAIN',
                'expression' => $executeTime,
                'class' => 'CorporateTrainingBundle\Biz\ProjectPlan\Job\ProjectPlanMailNotificationJob',
                'args' => array(
                    'projectPlanId' => $course['projectPlanId'],
                    'template' => 'offline_course_task',
                    'params' => array(
                        'courseTitle' => $course['title'],
                        'taskTitle' => $task['title'],
                        'place' => $task['place'],
                        'startTime' => date('Y-m-d H:i:s', $task['startTime']),
                    ),
                ),
                'misfire_policy' => 'executing',
            );

            $this->getSchedulerService()->register($job);
        }
    }

    public function onOfflineCourseUpdateTask(Event $event)
    {
        $task = $event->getSubject();
        $this->changeTime($task);
    }

    public function onOfflineCourseDeleteTask(Event $event)
    {
        $task = $event->getSubject();
        $this->changeOfflineCourseMember($task);
        $this->getOfflineCourseTaskService()->deleteTaskResultByTaskId($task['id']);
        $this->changeTaskNum($task);
        $this->changeTime($task);
        $this->changeTaskSeq($task);
    }

    public function onOfflineCourseSetTeacher(Event $event)
    {
        $offlineCourse = $event->getSubject();
        $oldOfflineCourse = $event->getArgument('oldOfflineCourse');
        $teacherIds = array_intersect($offlineCourse['teacherIds'], $oldOfflineCourse['teacherIds']);
        $teacherIds = array_diff($offlineCourse['teacherIds'], $teacherIds);

        foreach ($teacherIds as $teacher) {
            $this->getNotificationService()->notify($teacher, 'offline_course_set_teacher', $offlineCourse);
        }
    }

    protected function changeOfflineCourseMember($task)
    {
        $offlineCourse = $this->getOfflineCourseService()->getOfflineCourse($task['offlineCourseId']);
        $projectPlanMembers = $this->getProjectPlanMemberService()->findMembersByProjectPlanId($offlineCourse['projectPlanId']);
        foreach ($projectPlanMembers as  $projectPlanMember) {
            $offlineCourseMember = $this->getOfflineCourseMemberService()->getMemberByOfflineCourseIdAndUserId($task['offlineCourseId'], $projectPlanMember['userId']);
            $taskResult = $this->getOfflineCourseTaskService()->getTaskResultByTaskIdAndUserId($task['id'], $projectPlanMember['userId']);
            if ('finish' == $taskResult['status']) {
                $this->getOfflineCourseMemberService()->waveLearnedNum($offlineCourseMember['id'], -1);
            }
        }
    }

    protected function changeTaskNum($task)
    {
        $this->getOfflineCourseService()->updateOfflineCourse($task['offlineCourseId'], array('taskNum' => $this->getOfflineCourseTaskService()->countTasks(array('offlineCourseId' => $task['offlineCourseId']))));
    }

    protected function changeTime($task)
    {
        $tasks = $this->getOfflineCourseTaskService()->searchTasks(array('offlineCourseId' => $task['offlineCourseId'], 'type' => 'offlineCourse'), array(), 0, PHP_INT_MAX);
        $tasksStartTime = ArrayToolkit::column($tasks, 'startTime');
        asort($tasksStartTime);
        $tasksEndTime = ArrayToolkit::column($tasks, 'endTime');
        arsort($tasksEndTime);

        $startTime = empty($tasksStartTime) ? 0 : reset($tasksStartTime);
        $endTime = empty($tasksEndTime) ? 0 : reset($tasksEndTime);
        $offlineCourse = $this->getOfflineCourseService()->updateOfflineCourse($task['offlineCourseId'], array('startTime' => $startTime, 'endTime' => $endTime));
        $item = $this->getProjectPlanService()->getProjectPlanItemByProjectPlanIdAndTargetIdAndTargetType($offlineCourse['projectPlanId'], $offlineCourse['id'], 'offline_course');
        $this->getProjectPlanService()->updateProjectPlanItemTime($item['id'], array('startTime' => $startTime, 'endTime' => $endTime));
    }

    protected function changeTaskSeq($task)
    {
        $tasks = $this->getOfflineCourseTaskService()->searchTasks(
            array('offlineCourseId' => $task['offlineCourseId']),
            array('seq' => 'ASC'),
            0,
            PHP_INT_MAX
        );
        $taskIds = ArrayToolkit::column($tasks, 'id');
        $this->getOfflineCourseTaskService()->sortTasks($taskIds);
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineCourse\Service\Impl\TaskServiceImpl
     */
    protected function getOfflineCourseTaskService()
    {
        return $this->getBiz()->service('CorporateTrainingBundle:OfflineCourse:TaskService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineCourse\Service\Impl\OfflineCourseServiceImpl
     */
    protected function getOfflineCourseService()
    {
        return $this->getBiz()->service('CorporateTrainingBundle:OfflineCourse:OfflineCourseService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineCourse\Service\Impl\MemberServiceImpl
     */
    protected function getOfflineCourseMemberService()
    {
        return $this->getBiz()->service('CorporateTrainingBundle:OfflineCourse:MemberService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\Impl\ProjectPlanServiceImpl
     */
    protected function getProjectPlanService()
    {
        return $this->getBiz()->service('CorporateTrainingBundle:ProjectPlan:ProjectPlanService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\Impl\MemberServiceImpl
     */
    protected function getProjectPlanMemberService()
    {
        return $this->getBiz()->service('CorporateTrainingBundle:ProjectPlan:MemberService');
    }

    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }

    /**
     * @return \Biz\User\Service\Impl\NotificationServiceImpl
     */
    protected function getNotificationService()
    {
        return $this->getBiz()->service('User:NotificationService');
    }

    /**
     * @return SchedulerService
     */
    protected function getSchedulerService()
    {
        return $this->getBiz()->service('Scheduler:SchedulerService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }
}
