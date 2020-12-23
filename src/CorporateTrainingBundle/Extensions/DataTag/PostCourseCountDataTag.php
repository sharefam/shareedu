<?php

namespace CorporateTrainingBundle\Extensions\DataTag;

use AppBundle\Extensions\DataTag\DataTag;
use AppBundle\Extensions\DataTag\BaseDataTag;

class PostCourseCountDataTag extends BaseDataTag implements DataTag
{
    public function getData(array $arguments)
    {
        if (!isset($arguments['postId'])) {
            throw new \InvalidArgumentException($this->getServiceKernel()->trans('PostId参数缺失'));
        }

        $postCourse = $this->getPostCourseService()->findPostCoursesByPostId($arguments['postId']);

        return count($postCourse);
    }

    public function getPostCourseService()
    {
        return $this->getServiceKernel()->getBiz()->service('CorporateTrainingBundle:PostCourse:PostCourseService');
    }
}
