<?php

namespace CorporateTrainingBundle\Biz\Course\Service\Impl;

use Biz\Course\Service\Impl\MemberServiceImpl as BaseService;
use CorporateTrainingBundle\Biz\Course\Service\MemberService;

class MemberServiceImpl extends BaseService implements MemberService
{
    public function countLearnedStudentsByCourseId($courseId)
    {
        return $this->getMemberDao()->countLearnedMembers(array('courseId' => $courseId));
    }

    public function countLearnedMember($conditions)
    {
        return $this->getMemberDao()->countLearnedMembers($conditions);
    }

    public function getUserCourseFinishedTimeByCourseIdAndUserId($courseId, $userId)
    {
        return $this->getMemberDao()->getFinishedTimeByCourseIdAndUserId($courseId, $userId);
    }
}
