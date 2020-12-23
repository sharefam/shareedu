<?php

namespace CorporateTrainingBundle\Biz\ProjectPlan\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;

class ProjectPlanMailNotificationJob extends AbstractJob
{
    public function execute()
    {
        $jobArgs = $this->args;
        $projectPlanId = $jobArgs['projectPlanId'];
        $template = $jobArgs['template'];
        $params = $jobArgs['params'];

        $to = array(
            'type' => 'project_plan',
            'projectPlanId' => $projectPlanId,
            'startNum' => 0,
            'perPageNum' => 20,
        );

        $content = array(
            'template' => $template,
            'params' => $params,
        );

        $this->biz->offsetGet('notification_email')->send($to, $content);
    }
}
