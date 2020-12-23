<?php

namespace CorporateTrainingBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\User\Service\UserService;
use CorporateTrainingBundle\Biz\Post\Service\PostService;

class Me extends AbstractResource
{
    public function get(ApiRequest $request)
    {
        $user = $this->getUserService()->getUser($this->getCurrentUser()->getId());
        $profile = $this->getUserService()->getUserProfile($user['id']);
        $user = array_merge($profile, $user);
        $user = $this->buildUserPost($user);

        return $user;
    }

    protected function buildUserPost($user)
    {
        if (empty($user['postId'])) {
            return $user;
        }

        $post = $this->getPostService()->getPost($user['postId']);
        $user['postName'] = $post['name'];

        return $user;
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->service('User:UserService');
    }

    /**
     * @return PostService
     */
    protected function getPostService()
    {
        return $this->service('CorporateTrainingBundle:Post:PostService');
    }
}
