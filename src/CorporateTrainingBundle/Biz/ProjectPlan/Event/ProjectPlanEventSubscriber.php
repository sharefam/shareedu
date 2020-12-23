<?php

namespace CorporateTrainingBundle\Biz\ProjectPlan\Event;

use AppBundle\Common\ArrayToolkit;
use Biz\System\Service\SettingService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Event\EventSubscriber;
use Codeages\Biz\Framework\Queue\Service\QueueService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProjectPlanEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'projectPlan.item.set' => 'onProjectPlanItemSet',
            'projectPlan.item.delete' => 'onProjectPlanItemDelete',
        );
    }

    public function onProjectPlanItemSet(Event $event)
    {
        $projectPlanId = $event->getSubject();
        $params = $event->getArgument('params');
        $projectPlan = $this->getProjectPlanService()->getProjectPlan($projectPlanId);

        if (!empty($params['startTime'])) {
            $startTime = strtotime($params['startTime']);
            if (($projectPlan['startTime'] > 0 && $startTime < $projectPlan['startTime']) || $projectPlan['startTime'] <= 0) {
                $this->getProjectPlanService()->updateProjectPlan($projectPlan['id'], array('startTime' => $startTime));
            }
        }

        if (!empty($params['endTime'])) {
            $endTime = strtotime($params['endTime']);
            if ($endTime > $projectPlan['endTime']) {
                $this->getProjectPlanService()->updateProjectPlan($projectPlan['id'], array('endTime' => $endTime));
            }
        }

        $this->sendMailNotification($projectPlan);
    }

    protected function sendMailNotification($projectPlan)
    {
        $mailNotification = $this->getSettingService()->get('mail_notification', array());

        if (empty($mailNotification['project_plan_content'])) {
            return;
        }

        global $kernel;
        $url = $kernel->getContainer()->get('router')->generate('study_center_my_task_training_list', array(), true);

        $to = array(
            'type' => 'project_plan',
            'projectPlanId' => $projectPlan['id'],
            'startNum' => 0,
            'perPageNum' => 20,
        );
        $content = array(
            'template' => 'project_plan_update',
            'params' => array(
                'projectPlanName' => $projectPlan['name'],
                'url' => $url,
            ),
        );

        $this->getBiz()->offsetGet('notification_email')->send($to, $content);
    }

    public function onProjectPlanItemDelete(Event $event)
    {
        $projectPlanId = $event->getSubject();
        $projectPlanItems = $this->getProjectPlanService()->searchProjectPlanItems(
            array('projectPlanId' => $projectPlanId),
            array('seq' => 'ASC'),
            0,
            PHP_INT_MAX
        );
        $projectPlanItemIds = ArrayToolkit::column($projectPlanItems, 'id');
        $this->getProjectPlanService()->sortItems($projectPlanItemIds);
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\Impl\ProjectPlanServiceImpl
     */
    protected function getProjectPlanService()
    {
        return $this->getBiz()->service('CorporateTrainingBundle:ProjectPlan:ProjectPlanService');
    }

    /**
     * @return QueueService
     */
    protected function getQueueService()
    {
        return $this->getBiz()->service('Queue:QueueService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }
}
