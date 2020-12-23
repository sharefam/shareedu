<?php

namespace CorporateTrainingBundle\Biz\ProjectPlan\Event;

use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MemberStatisticDataEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'project.plan.offline.course.attended' => array('onOfflineCourseAttended', 255),
            'project.plan.offline.course.unattended' => 'onOfflineCourseUnattended',
            'project.plan.offline.course.signin' => 'onOfflineCourseSignIn',
            'project.plan.offline.course.pass.homework' => 'onOfflineCoursePassHomework',
            'project.plan.offline.course.unpass.homework' => 'onOfflineCourseUnpassHomework',
            'offline.exam.mark.pass' => 'onOfflineExamMarkPass',
            'offline.exam.mark.unpass' => 'onOfflineExamMarkUnPass',
        );
    }

    public function onOfflineCourseAttended(Event $event)
    {
        $taskResult = $event->getSubject();
        $oldTaskResult = $event->getArgument('oldTaskResult');
        $task = $this->getOfflineCourseTaskService()->getTask($taskResult['taskId']);

        $courseMember = $this->getofflineCourseMemberService()->getMemberByOfflineCourseIdAndUserId($taskResult['offlineCourseId'], $taskResult['userId']);

        if ($oldTaskResult['attendStatus'] == 'unattended' && $taskResult['attendStatus'] == 'attended') {
            if ($task['hasHomework'] == 0 || $taskResult['homeworkStatus'] == 'passed') {
                $this->getOfflineCourseMemberService()->waveLearnedNum($courseMember['id'], 1);
            }
        }
    }

    public function onOfflineCourseUnattended(Event $event)
    {
        $taskResult = $event->getSubject();
        $oldTaskResult = $event->getArgument('oldTaskResult');
        $task = $this->getOfflineCourseTaskService()->getTask($taskResult['taskId']);

        $offlineCourseMember = $this->getofflineCourseMemberService()->getMemberByOfflineCourseIdAndUserId($taskResult['offlineCourseId'], $taskResult['userId']);

        if (!empty($oldTaskResult) && $oldTaskResult['attendStatus'] == 'attended' && $taskResult['attendStatus'] == 'unattended') {
            if ($task['hasHomework'] == 0 || $taskResult['homeworkStatus'] == 'passed') {
                $this->getOfflineCourseMemberService()->waveLearnedNum($offlineCourseMember['id'], -1);
            }
        }

        $this->getOfflineCourseTaskService()->updateTaskResult($taskResult['id'], array('finishedTime' => 0));
    }

    public function onOfflineCourseSignIn(Event $event)
    {
        $taskResult = $event->getSubject();
        $offlineCourse = $this->getOfflineCourseService()->getOfflineCourse($taskResult['offlineCourseId']);
        $task = $this->getOfflineCourseTaskService()->getTask($taskResult['taskId']);

        $courseMember = $this->getOfflineCourseMemberService()->getMemberByOfflineCourseIdAndUserId($offlineCourse['id'], $taskResult['userId']);

        if ($task['hasHomework'] == 0 || $taskResult['homeworkStatus'] == 'passed') {
            $this->getOfflineCourseMemberService()->waveLearnedNum($courseMember['id'], 1);
        }
    }

    public function onOfflineCoursePassHomework(Event $event)
    {
        $taskResult = $event->getSubject();
        $oldTaskResult = $event->getArgument('oldTaskResult');

        $offlineCourseMember = $this->getofflineCourseMemberService()->getMemberByOfflineCourseIdAndUserId($taskResult['offlineCourseId'], $taskResult['userId']);

        $taskResult['task'] = $this->getOfflineCourseTaskService()->getTask($taskResult['taskId']);
        $this->getNotificationService()->notify($taskResult['userId'], 'offline_course_homework_mark', $taskResult);
        if ($oldTaskResult['homeworkStatus'] != $taskResult['homeworkStatus']) {
            if (in_array($oldTaskResult['homeworkStatus'], array('submitted', 'unpassed')) && $taskResult['homeworkStatus'] == 'passed') {
                if ($taskResult['attendStatus'] == 'attended') {
                    $this->getOfflineCourseMemberService()->waveLearnedNum($offlineCourseMember['id'], 1);
                }
            }
        }
    }

    public function onOfflineCourseUnpassHomework(Event $event)
    {
        $taskResult = $event->getSubject();
        $oldTaskResult = $event->getArgument('oldTaskResult');

        $offlineCourseMember = $this->getofflineCourseMemberService()->getMemberByOfflineCourseIdAndUserId($taskResult['offlineCourseId'], $taskResult['userId']);

        $taskResult['task'] = $this->getOfflineCourseTaskService()->getTask($taskResult['taskId']);
        $this->getNotificationService()->notify($taskResult['userId'], 'offline_course_homework_mark', $taskResult);
        if ($oldTaskResult['homeworkStatus'] != $taskResult['homeworkStatus']) {
            if ($oldTaskResult['homeworkStatus'] == 'passed' && in_array($taskResult['homeworkStatus'], array('submitted', 'unpassed'))) {
                $this->getOfflineCourseMemberService()->waveLearnedNum($offlineCourseMember['id'], -1);
            }
        }

        $this->getOfflineCourseTaskService()->updateTaskResult($taskResult['id'], array('finishedTime' => 0));
    }

    public function onOfflineExamMarkPass(Event $event)
    {
        $member = $event->getSubject();
        $offlineExam = $this->getOfflineExamService()->getOfflineExam($member['offlineExamId']);

        $member['exam'] = $offlineExam;
        $this->getNotificationService()->notify($member['userId'], 'offline_exam_mark', $member);
    }

    public function onOfflineExamMarkUnPass(Event $event)
    {
        $member = $event->getSubject();
        $offlineExam = $this->getOfflineExamService()->getOfflineExam($member['offlineExamId']);

        $member['exam'] = $offlineExam;
        $this->getNotificationService()->notify($member['userId'], 'offline_exam_mark', $member);
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineCourse\Service\Impl\TaskServiceImpl
     */
    protected function getOfflineCourseTaskService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineCourse:TaskService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineCourse\Service\Impl\MemberServiceImpl
     */
    protected function getOfflineCourseMemberService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineCourse:MemberService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineCourse\Service\Impl\OfflineCourseServiceImpl
     */
    protected function getOfflineCourseService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineCourse:OfflineCourseService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineExam\Service\Impl\OfflineExamServiceImpl
     */
    protected function getOfflineExamService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineExam:OfflineExamService');
    }

    protected function createService($alias)
    {
        return $this->getBiz()->service($alias);
    }

    /**
     * @return \Biz\User\Service\Impl\NotificationServiceImpl
     */
    protected function getNotificationService()
    {
        return $this->getBiz()->service('User:NotificationService');
    }
}
