<?php

namespace CorporateTrainingBundle\Extensions\DataTag;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Extensions\DataTag\DataTag;
use AppBundle\Extensions\DataTag\BaseDataTag;

class PostSelectTreeDataTag extends BaseDataTag implements DataTag
{
    public function getData(array $arguments)
    {
        $postGroups = $this->getPostService()->searchPostGroups(array(), array('seq' => 'ASC'), 0, PHP_INT_MAX);
        $posts = $this->getPostService()->searchPosts(array(), array('seq' => 'ASC'), 0, PHP_INT_MAX);
        $posts = ArrayToolkit::group($posts, 'groupId');

        return array('posts' => $posts, 'groups' => $postGroups);
    }

    public function getPostService()
    {
        return $this->getServiceKernel()->getBiz()->service('CorporateTrainingBundle:Post:PostService');
    }
}
