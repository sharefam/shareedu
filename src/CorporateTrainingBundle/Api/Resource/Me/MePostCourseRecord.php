<?php

namespace CorporateTrainingBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\Task\Service\TaskResultService;
use Biz\User\Service\Impl\UserServiceImpl;
use CorporateTrainingBundle\Biz\PostCourse\Service\PostCourseService;

class MePostCourseRecord extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $user = $this->getCurrentUser();
        $total = $this->getPostCourseService()->countPostCourses(array('postId' => $user['postId']));

        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $postCourses = $this->getPostCourseService()->searchPostCourses(
            array('postId' => $user['postId']),
            array('id' => 'DESC'),
            $offset,
            $limit
        );

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
            $postCourses[$key]['courseName'] = empty($course) ? '' : $course['title'];
            $postCourses[$key]['teacherName'] = empty($course) ? '' : $this->getTeacherName($course['teacherIds']);
            $learnedCompulsoryTaskNum = empty($member) ? 0 : $member['learnedCompulsoryTaskNum'];
            $totalLeanTime = $this->getTaskResultService()->sumLearnTimeByCourseIdAndUserId($courseId, $user['id']);
            $postCourses[$key]['totalLearnTime'] = empty($totalLeanTime) ? 0 : $totalLeanTime;
            $postCourses[$key]['progress'] = $this->getPostCourseProgress($learnedCompulsoryTaskNum, $course['compulsoryTaskNum']);
        }

        $this->getOCUtil()->multiple($postCourses, array('courseId'), 'course');

        return $this->makePagingObject(array_values($postCourses), $total, $offset, $limit);
    }

    protected function getPostCourseProgress($learnedCompulsoryTaskNum, $compulsoryTaskNum)
    {
        if (empty($compulsoryTaskNum)) {
            return 0;
        }

        return round(($learnedCompulsoryTaskNum / $compulsoryTaskNum) * 100, 0);
    }

    protected function getTeacherName($teacherIds)
    {
        $teacher = $this->getUserService()->getUserProfile($teacherIds[0]);

        if (empty($teacher['truename'])) {
            $teacher = $this->getUserService()->getUser($teacherIds[0]);
        }

        return isset($teacher['truename']) ? $teacher['truename'] : $teacher['nickname'];
    }

    /**
     * @return PostCourseService
     */
    protected function getPostCourseService()
    {
        return $this->service('CorporateTrainingBundle:PostCourse:PostCourseService');
    }

    /**
     * @return UserServiceImpl
     */
    protected function getUserService()
    {
        return $this->service('User:UserService');
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
