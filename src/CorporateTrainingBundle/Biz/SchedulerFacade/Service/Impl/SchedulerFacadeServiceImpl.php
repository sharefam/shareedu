<?php

namespace CorporateTrainingBundle\Biz\SchedulerFacade\Service\Impl;

use Codeages\Biz\Framework\Scheduler\Service\Impl\SchedulerServiceImpl;
use CorporateTrainingBundle\Biz\SchedulerFacade\Service\SchedulerFacadeService;

class SchedulerFacadeServiceImpl extends SchedulerServiceImpl implements SchedulerFacadeService
{
    public function setNextFiredTime($jobId, $nextFiredTime)
    {
        $job = $this->getJob($jobId);

        if ($nextFiredTime > time() && !empty($job)) {
            return $this->getJobDao()->update($job['id'], array('next_fire_time' => $nextFiredTime));
        }

        return false;
    }

    public function getJob($jobId)
    {
        return $this->getJobDao()->get($jobId);
    }
}
