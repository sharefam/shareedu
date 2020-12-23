<?php

namespace CorporateTrainingBundle\Biz\OfflineExam\Job;

use Biz\System\Service\SettingService;
use CorporateTrainingBundle\Biz\DingTalk\Job\AbstractDingTalkNotificationJob;
use CorporateTrainingBundle\Biz\ProjectPlan\Service\ProjectPlanService;

class OfflineExamDingTalkNotificationJob extends AbstractDingTalkNotificationJob
{
    protected function canSend()
    {
        if (empty($this->args['exam'])) {
            throw new \InvalidArgumentException('Lack of required fields');
        }

        $exam = $this->args['exam'];

        $projectPlan = $this->getProjectPlanService()->getProjectPlan($exam['projectPlanId']);

        return 'published' == $projectPlan['status'];
    }

    protected function buildNotificationData()
    {
        $exam = $this->args['exam'];
        $to = array(
            'type' => 'project_plan',
            'projectPlanId' => $exam['projectPlanId'],
            'startNum' => 0,
            'perPageNum' => 20,
        );
        $content = array(
            'template' => $this->args['template'],
            'params' => array(
                'targetId' => $exam['id'],
                'batch' => 'offline_exam_remind'.$exam['id'].time(),
                'url' => $this->args['url'],
                'title' => $exam['title'],
                'place' => $exam['place'],
                'startTime' => $exam['startTime'],
                'endTime' => $exam['endTime'],
            ),
        );

        return array($to, $content);
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }

    /**
     * @return ProjectPlanService
     */
    protected function getProjectPlanService()
    {
        return $this->biz->service('CorporateTrainingBundle:ProjectPlan:ProjectPlanService');
    }
}
