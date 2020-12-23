<?php

namespace CorporateTrainingBundle\Biz\PostCourse\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use CorporateTrainingBundle\Biz\PostCourse\Service\PostCourseService;
use CorporateTrainingBundle\Biz\PostCourse\Service\UserPostCourseService;

class UserPostCourseServiceImpl extends BaseService implements UserPostCourseService
{
    public function isCourseBelongToUserPostCourse($courseId, $user)
    {
        if (empty($user['postId'])) {
            return false;
        }

        $postCourses = $this->getPostCourseService()->findPostCoursesByPostId($user['postId']);

        return in_array($courseId, ArrayToolkit::column($postCourses, 'courseId'));
    }

    /**
     * @return PostCourseService
     */
    protected function getPostCourseService()
    {
        return $this->createService('CorporateTrainingBundle:PostCourse:PostCourseService');
    }
}
