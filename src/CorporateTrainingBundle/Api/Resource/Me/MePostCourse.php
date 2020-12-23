<?php

namespace CorporateTrainingBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\Task\Service\TaskResultService;
use CorporateTrainingBundle\Biz\PostCourse\Service\PostCourseService;

class MePostCourse extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $user = $this->getCurrentUser();
        if (empty($user) || empty($user['postId'])) {
            return array();
        }

        $postCourses = $this->getPostCourseService()->findPostCoursesByPostId($user['postId']);
        $members = $this->getCourseMemberService()->searchMembers(
            array('userId' => $user['id'], 'courseIds' => ArrayToolkit::column($postCourses, 'courseId')),
            array('id' => 'DESC'),
            0,
            PHP_INT_MAX
        );
        $members = ArrayToolkit::index($members, 'courseId');
        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($postCourses, 'courseId'));
        $courses = ArrayToolkit::index($courses, 'id');
        foreach ($postCourses as $key => $postCourse) {
            $courseId = $postCourse['courseId'];
            $course = empty($courses[$courseId]) ? array() : $courses[$courseId];
            $member = empty($members[$courseId]) ? array() : $members[$courseId];
            $postCourses[$key]['learnedCompulsoryTaskNum'] = empty($member) ? 0 : $member['learnedCompulsoryTaskNum'];
            $postCourses[$key]['compulsoryTaskNum'] = $course['compulsoryTaskNum'];
            $totalLeanTime = $this->getTaskResultService()->sumLearnTimeByCourseIdAndUserId($courseId, $user['id']);
            $postCourses[$key]['totalLearnTime'] = empty($totalLeanTime) ? 0 : $totalLeanTime;
        }
        $this->getOCUtil()->multiple($postCourses, array('courseSetId'), 'courseSet');

        return $postCourses;
    }

    /**
     * @return PostCourseService
     */
    protected function getPostCourseService()
    {
        return $this->service('CorporateTrainingBundle:PostCourse:PostCourseService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->service('Course:MemberService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
    {
        return $this->service('Task:TaskResultService');
    }
}
