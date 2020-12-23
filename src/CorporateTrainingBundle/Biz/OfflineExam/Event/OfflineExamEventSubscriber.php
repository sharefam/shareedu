<?php

namespace CorporateTrainingBundle\Biz\OfflineExam\Event;

use Biz\System\Service\LogService;
use Biz\System\Service\SettingService;
use Biz\User\Service\UserService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Event\EventSubscriber;
use CorporateTrainingBundle\Biz\OfflineExam\Service\MemberService;
use CorporateTrainingBundle\Biz\OfflineExam\Service\OfflineExamService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OfflineExamEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'offline.exam.mark.pass' => 'onOfflineExamMarkPass',
            'offline.exam.mark.unpass' => 'onOfflineExamMarkUnPass',
        );
    }

    public function onOfflineExamMarkPass(Event $event)
    {
        $member = $event->getSubject();
        $this->sendNotification($member);
    }

    public function onOfflineExamMarkUnPass(Event $event)
    {
        $member = $event->getSubject();
        $this->sendNotification($member);
    }

    protected function sendNotification($member)
    {
        if ('none' == $member['status']) {
            return;
        }

        $mailNotification = $this->getSettingService()->get('mail_notification', array());

        $types = array();

        if (!empty($mailNotification['exam'])) {
            $types[] = 'email';
        }

        $dingtalkNotification = $this->getSettingService()->get('dingtalk_notification', array());

        if (!empty($dingtalkNotification['project_plan_offline_exam_result'])) {
            $types[] = 'dingtalk';
        }

        if (empty($types)) {
            return;
        }

        foreach ($types as $type) {
            list($to, $content) = $this->buildNotificationData($member, $type);
            $this->getBiz()->offsetGet('notification_'.$type)->send($to, $content);
        }
    }

    protected function buildNotificationData($member, $type)
    {
        $exam = $this->getOfflineExamService()->getOfflineExam($member['offlineExamId']);

        $to = array(
            'type' => 'user',
            'userIds' => array($member['userId']),
            'startNum' => 0,
            'perPageNum' => 20,
        );

        global $kernel;
        $url = $kernel->getContainer()->get('router')->generate('study_center_my_task_training_list', array(), true);
        $url = ('email' === $type) ? $url : $kernel->getContainer()->get('router')->generate('study_record_project_plan', array('userId' => $member['userId']), true);

        $template = ('email' === $type) ? 'exam_result' : 'offline_exam_result';

        $content = array(
            'template' => $template,
            'params' => array(
                'targetId' => $exam['id'],
                'batch' => 'offline_exam_result'.$exam['id'].time(),
                'url' => $url,
                'examName' => $exam['title'],
                'score' => $member['score'],
                'totalScore' => $exam['score'],
                'status' => ('passed' == $member['status']) ? '通过' : '未通过',
            ),
        );

        return array($to, $content);
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->getBiz()->service('CorporateTrainingBundle:User:UserService');
    }

    /**
     * @return MemberService
     */
    protected function getMemberService()
    {
        return $this->getBiz()->service('CorporateTrainingBundle:OfflineExam:MemberService');
    }

    /**
     * @return OfflineExamService
     */
    protected function getOfflineExamService()
    {
        return $this->getBiz()->service('CorporateTrainingBundle:OfflineExam:OfflineExamService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->getBiz()->service('System:LogService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }
}
