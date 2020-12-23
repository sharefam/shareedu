<?php

namespace CorporateTrainingBundle\Biz\Course\Service;

use Biz\Course\Service\MemberService as BaseService;

interface MemberService extends BaseService
{
    public function countLearnedStudentsByCourseId($courseId);

    public function getUserCourseFinishedTimeByCourseIdAndUserId($courseId, $userId);
}
