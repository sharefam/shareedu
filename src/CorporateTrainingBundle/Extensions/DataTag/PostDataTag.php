<?php

namespace CorporateTrainingBundle\Extensions\DataTag;

use AppBundle\Extensions\DataTag\DataTag;
use AppBundle\Extensions\DataTag\BaseDataTag;

class PostDataTag extends BaseDataTag implements DataTag
{
    public function getData(array $arguments)
    {
        if (!isset($arguments['postId'])) {
            throw new \InvalidArgumentException($this->getServiceKernel()->trans('PostId参数缺失'));
        }

        return $this->getPostService()->getPost($arguments['postId']);
    }

    public function getPostService()
    {
        return $this->getServiceKernel()->getBiz()->service('CorporateTrainingBundle:Post:PostService');
    }
}
