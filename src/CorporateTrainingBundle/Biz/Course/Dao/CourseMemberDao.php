<?php

namespace CorporateTrainingBundle\Biz\Course\Dao;

use Biz\Course\Dao\CourseMemberDao as BaseCourseMemberDao;

interface CourseMemberDao extends BaseCourseMemberDao
{
    public function findMembersByCourseIdsAndRole($courseIds, $role);

    public function getFinishedTimeByCourseIdAndUserId($courseId, $userId);
}
