<?php

namespace CorporateTrainingBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\Task\Service\TaskResultService;

class MeCourse extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $isLearned = $request->query->get('isLearned', 0);
        $courseTitle = $request->query->get('courseTitle', '');

        $courses = $this->findUserCoursesByLearnStatus($isLearned, $offset, $limit);
        if (!empty($courseTitle)) {
            $courses = $this->findUserCoursesByTitle($courseTitle, $offset, $limit);
        }

        $this->getOCUtil()->multiple($courses, array('courseSetId'), 'courseSet');

        return $this->makePagingObject($courses, count($courses), $offset, $limit);
    }

    protected function findUserCoursesByLearnStatus($isLearned, $offset, $limit)
    {
        if (empty($isLearned)) {
            return $this->findUserLearningCourses($offset, $limit);
        }

        return $this->findUserLearnedCourses($offset, $limit);
    }

    protected function findUserLearnedCourses($offset, $limit)
    {
        $courses = $this->getCourseService()->findUserLearnedCourses(
            $this->getCurrentUser()->getId(),
            $offset,
            $limit,
            array('classroomId' => 0)
        );

        foreach ($courses as $key => $course) {
            $totalLeanTime = $this->getTaskResultService()->sumLearnTimeByCourseIdAndUserId($course['id'], $this->getCurrentUser()->getId());
            $courses[$key]['totalLearnTime'] = empty($totalLeanTime) ? 0 : $totalLeanTime;
        }

        return $courses;
    }

    protected function findUserLearningCourses($offset, $limit)
    {
        $courses = $this->getCourseService()->findUserLearningCourses(
            $this->getCurrentUser()->getId(),
            $offset,
            $limit,
            array('classroomId' => 0)
        );

        $members = $this->getCourseMemberService()->searchMembers(
            array('courseIds' => ArrayToolkit::column($courses, 'id'), 'userId' => $this->getCurrentUser()->getId()),
            array(),
            0,
            PHP_INT_MAX
        );
        $members = ArrayToolkit::index($members, 'courseId');
        foreach ($courses as $key => $course) {
            $member = empty($members[$course['id']]) ? array() : $members[$course['id']];
            $courses[$key]['learnedCompulsoryTaskNum'] = empty($member) ? 0 : $member['learnedCompulsoryTaskNum'];
        }

        return $courses;
    }

    protected function findUserCoursesByTitle($title, $offset, $limit)
    {
        $conditions['status'] = 'published';
        $conditions['classroomId'] = 0;
        $conditions['joinedType'] = 'course';
        $conditions['userId'] = $this->getCurrentUser()->getId();
        $conditions['role'] = 'student';
        $members = $this->getCourseMemberService()->searchMembers(
            $conditions,
            array('lastLearnTime' => 'DESC'),
            0,
            PHP_INT_MAX
        );

        $courseIds = ArrayToolkit::column($members, 'courseId');
        if (empty($courseIds)) {
            return array();
        }

        return $this->getCourseService()->searchCourses(
            array('courseIds' => $courseIds, 'titleLike' => $title),
            array('id' => 'DESC'),
            $offset,
            $limit
        );
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
