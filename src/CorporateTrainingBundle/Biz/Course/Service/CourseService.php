<?php

namespace CorporateTrainingBundle\Biz\Course\Service;

use Biz\Course\Service\CourseService as BaseService;

interface CourseService extends BaseService
{
    public function findCourseItemsByUserId($courseId, $userId, $limitNum = 0);

    public function findStudentsByCourseIds($courseIds);

    public function findTeachersByCourseIds($courseIds);
}
