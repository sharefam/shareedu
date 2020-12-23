<?php

namespace CorporateTrainingBundle\Biz\PostCourse\Service;

interface UserPostCourseService
{
    public function isCourseBelongToUserPostCourse($courseId, $user);
}
