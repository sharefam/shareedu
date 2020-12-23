<?php

namespace CorporateTrainingBundle\Biz\Task\Service;

use Biz\Task\Service\TaskService as BaseService;

interface TaskService extends BaseService
{
    public function findTaskByCourseIdAndTaskTypeAndTimeRange($courseId, $type, $time);

    public function findTasksFetchActivityAndResultByCourseIdAndUserId($courseId, $userId);

    public function getTaskByActivityId($activityId);
}
