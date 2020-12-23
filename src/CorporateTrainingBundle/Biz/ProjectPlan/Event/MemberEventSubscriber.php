<?php

namespace CorporateTrainingBundle\Biz\ProjectPlan\Event;

use AppBundle\Common\ArrayToolkit;
use Biz\System\Service\SettingService;
use Biz\User\Service\NotificationService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Event\EventSubscriber;
use Codeages\Biz\Framework\Queue\Service\QueueService;
use CorporateTrainingBundle\Biz\ProjectPlan\Service\MemberService;
use CorporateTrainingBundle\Biz\ProjectPlan\Service\ProjectPlanService;
use CorporateTrainingBundle\Biz\User\Service\UserService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MemberEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'become_project_plan_member' => 'onBecomeProjectPlanMember',
            'reject_project_plan_apply' => 'onRejectProjectPlansApply',
            'pass_project_plan_applies' => 'onPassProjectPlanApplies',
            'reject_project_plan_applies' => 'onRejectProjectPlanApplies',
            'project_plan.attend.enrollment' => 'onProjectPlanAttendEnrollment',
        );
    }

    public function onBecomeProjectPlanMember(Event $event)
    {
        $member = $event->getSubject();

        $projectPlan = $this->getProjectPlanService()->getProjectPlan($member['projectPlanId']);

        if (empty($member) || empty($projectPlan) || 'published' != $projectPlan['status']) {
            return;
        }

        $this->getNotificationService()->notify($member['userId'], 'projectPlan_become_member', $projectPlan);
    }

    public function onRejectProjectPlansApply(Event $event)
    {
        $record = $event->getSubject();

        $projectPlan = $this->getProjectPlanService()->getProjectPlan($record['projectPlanId']);

        if (empty($record) || empty($projectPlan) || 'published' != $projectPlan['status']) {
            return;
        }
        $projectPlan['rejectedReason'] = $record['rejectedReason'];
        $this->getNotificationService()->notify($record['userId'], 'reject_project_plan_apply', $projectPlan);
    }

    public function onPassProjectPlanApplies(Event $event)
    {
        $recordIds = $event->getSubject();

        if (empty($recordIds)) {
            return;
        }

        $records = $this->getProjectPlanMemberService()->findEnrollmentRecordsByIds($recordIds);
        $userIds = ArrayToolkit::column($records, 'userId');
        $projectPlan = $this->getProjectPlanService()->getProjectPlan($records[0]['projectPlanId']);

        $this->sendNotification($userIds, $projectPlan, 'email_project_plan_enroll_result');
    }

    public function onRejectProjectPlanApplies(Event $event)
    {
        $recordIds = $event->getSubject();
        $records = $this->getProjectPlanMemberService()->findEnrollmentRecordsByIds($recordIds);
        $userIds = ArrayToolkit::column($records, 'userId');
        if (empty($userIds)) {
            return;
        }
        $projectPlan = $this->getProjectPlanService()->getProjectPlan($records[0]['projectPlanId']);
        $rejectedReason = empty($records[0]['rejectedReason']) ? '' : $records[0]['rejectedReason'];

        $this->sendNotification($userIds, $projectPlan, 'email_project_plan_enroll_reject', $rejectedReason);
    }

    public function onProjectPlanAttendEnrollment(Event $event)
    {
        $record = $event->getSubject();
        $projectPlan = $this->getProjectPlanService()->getProjectPlan($record['projectPlanId']);

        $this->sendNotification(array($record['userId']), $projectPlan, 'email_project_plan_enroll_result');
    }

    protected function sendNotification($userIds, $projectPlan, $template, $rejectedReason = '')
    {
        if (empty($userIds)) {
            return;
        }
        $mailNotification = $this->getSettingService()->get('mail_notification', array());
        $dingtalkNotification = $this->getSettingService()->get('dingtalk_notification', array());
        if (!empty($mailNotification['enroll'])) {
            $types[] = 'email';
        }

        if (!empty($dingtalkNotification['project_plan_apply'])) {
            $types[] = 'dingtalk';
        }
        if (empty($types)) {
            return;
        }

        global $kernel;
        $url = $kernel->getContainer()->get('router')->generate('project_plan_detail', array('id' => $projectPlan['id']), true);

        $to = array(
            'type' => 'user',
            'userIds' => $userIds,
            'startNum' => 0,
            'perPageNum' => 20,
        );
        $content = array(
            'template' => $template,
            'params' => array(
                'batch' => $template.$projectPlan['id'].time(),
                'targetId' => $projectPlan['id'],
                'imagePath' => empty($projectPlan['cover']['large']) ? '' : $projectPlan['cover']['large'],
                'projectPlanName' => $projectPlan['name'],
                'url' => $url,
                'rejectedReason' => $rejectedReason,
            ),
        );

        $this->getBiz()->offsetGet('notification_default')->send($types, $to, $content);
    }

    /**
     * @return ProjectPlanService
     */
    protected function getProjectPlanService()
    {
        return $this->getBiz()->service('CorporateTrainingBundle:ProjectPlan:ProjectPlanService');
    }

    /**
     * @return MemberService
     */
    protected function getProjectPlanMemberService()
    {
        return $this->getBiz()->service('CorporateTrainingBundle:ProjectPlan:MemberService');
    }

    /**
     * @return NotificationService
     */
    protected function getNotificationService()
    {
        return $this->getBiz()->service('User:NotificationService');
    }

    /**
     * @return QueueService
     */
    protected function getQueueService()
    {
        return $this->getBiz()->service('Queue:QueueService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }
}
