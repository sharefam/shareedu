<?php

namespace CorporateTrainingBundle\Biz\OfflineCourse\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface OfflineCourseDao extends GeneralDaoInterface
{
    public function findByIds($ids);

    public function findPublishedOfflineCoursesByTeacherIdAndTimeRange($teacherId, $timeRange);

    public function findPublishedCourseByUserIds($userIds);
}
