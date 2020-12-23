<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Course extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        if (!$course) {
            throw new NotFoundHttpException('教学计划不存在', null, ErrorCode::RESOURCE_NOT_FOUND);
        }

        $this->getOCUtil()->single($course, array('creator', 'teacherIds'));
        $this->getOCUtil()->single($course, array('courseSetId'), 'courseSet');

        $course['access'] = $this->getCourseService()->canJoinCourse($courseId);
        $course = $this->convertFields($course);

        return $course;
    }

    protected function convertFields($course)
    {
        $enableAudioStatus = $this->getCourseService()->isSupportEnableAudio($course['enableAudio']);
        $course['isAudioOn'] = $enableAudioStatus ? '1' : '0';
        unset($course['enableAudio']);

        return $course;
    }

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request)
    {
        $conditions = $request->query->all();
        $conditions['status'] = 'published';

        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $sort = $this->getSort($request);
        $courses = $this->service('Course:CourseService')->searchCourses(
            $conditions,
            $sort,
            $offset,
            $limit
        );

        $total = $this->service('Course:CourseService')->searchCourseCount($conditions);

        $this->getOCUtil()->multiple($courses, array('creator', 'teacherIds'));
        $this->getOCUtil()->multiple($courses, array('courseSetId'), 'courseSet');

        return $this->makePagingObject($courses, $total, $offset, $limit);
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->service('Classroom:ClassroomService');
    }

    /**
     * @return MemberService
     */
    protected function getMemberService()
    {
        return $this->service('Course:MemberService');
    }
}
