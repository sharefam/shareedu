<?php

namespace Biz\Course\Accessor;

use Biz\Accessor\AccessorAdapter;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\MemberService;
use CorporateTrainingBundle\Biz\ResourceScope\Service\ResourceAccessScopeService;

class JoinCourseMemberAccessor extends AccessorAdapter
{
    public function access($course)
    {
        $user = $this->getCurrentUser();
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        if (null === $user || !$user->isLogin()) {
            return $this->buildResult('user.not_login');
        }

        if ($user['locked']) {
            return $this->buildResult('user.locked', array('userId' => $user['id']));
        }

        if ($this->getCourseMemberService()->getCourseMember($course['id'], $user->getId())) {
            return $this->buildResult('member.member_exist', array('userId' => $user['id']));
        }

        if ($this->getCourseService()->canUserAutoJoinCourse($user, $course['id'])) {
            return null;
        }

        if (!$this->getResourceAccessScopeService()->canUserAccessResource('courseSet', $course['courseSetId'], $user['id']) && 1 == $courseSet['conditionalAccess']) {
            return $this->buildResult('resource.can_not_access', array('userId' => $user['id']));
        }

        return null;
    }

    /**
     * @return ResourceAccessScopeService
     */
    protected function getResourceAccessScopeService()
    {
        return $this->biz->service('CorporateTrainingBundle:ResourceScope:ResourceAccessScopeService');
    }

    /**
     * @return MemberService
     */
    private function getCourseMemberService()
    {
        return $this->biz->service('Course:MemberService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->biz->service('Course:CourseSetService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }

    protected function getUserPostCourseService()
    {
        return $this->biz->service('CorporateTrainingBundle:PostCourse:UserPostCourseService');
    }

    protected function getProjectPlanMemberService()
    {
        return $this->biz->service('CorporateTrainingBundle:ProjectPlan:MemberService');
    }
}
