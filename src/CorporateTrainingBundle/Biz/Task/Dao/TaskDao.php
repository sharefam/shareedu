<?php

namespace CorporateTrainingBundle\Biz\Task\Dao;

interface TaskDao
{
    public function findByCourseIdAndTaskTypeAndTimeRange($courseId, $type, $time);

    public function getByActivityId($activityId);
}
