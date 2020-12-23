<?php

namespace CorporateTrainingBundle\Extensions\DataTag\Tests;

use Biz\BaseTestCase;
use CorporateTrainingBundle\Extensions\DataTag\PostDataTag;

class PostDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $postGroup = array('name' => 'test group');
        $postGroup = $this->getPostService()->createPostGroup($postGroup);
        $post = array('name' => 'test post', 'groupId' => $postGroup['id'], 'code' => 'test');
        $post = $this->getPostService()->createPost($post);

        $postDataTag = new PostDataTag();
        $postData = $postDataTag->getData(array('postId' => $post['id']));

        $this->assertEquals($post['id'], $postData['id']);
    }

    protected function getPostService()
    {
        return $this->getServiceKernel()->getBiz()->service('CorporateTrainingBundle:Post:PostService');
    }
}
