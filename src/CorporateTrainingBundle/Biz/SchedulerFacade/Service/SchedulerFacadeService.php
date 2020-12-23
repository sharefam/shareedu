<?php

namespace CorporateTrainingBundle\Biz\SchedulerFacade\Service;

use Codeages\Biz\Framework\Scheduler\Service\SchedulerService as BaseService;

interface SchedulerFacadeService extends BaseService
{
    public function setNextFiredTime($jobId, $nextFiredTime);

    public function getJob($jobId);
}
