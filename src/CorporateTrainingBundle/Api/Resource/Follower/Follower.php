<?php

namespace CorporateTrainingBundle\Api\Resource\Follower;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\User\Service\UserService;

class Follower extends AbstractResource
{
    public function add(ApiRequest $request)
    {
        $userId = $request->request->get('userId');
        try {
            $result = $this->getUserService()->follow($this->getCurrentUser()->getId(), $userId);
        } catch (\Exception $e) {
            $result = false;
        }

        return array('followed' => empty($result) ? false : true);
    }

    public function remove(ApiRequest $request, $userId)
    {
        try {
            $result = $this->getUserService()->unFollow($this->getCurrentUser()->getId(), $userId);
        } catch (\Exception $e) {
            $result = false;
        }

        return array('cancelFollow' => empty($result) ? false : true);
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->service('User:UserService');
    }
}
