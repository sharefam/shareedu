<?php

namespace CorporateTrainingBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use CorporateTrainingBundle\Biz\User\Service\UserService;

class MeRelationship extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $userIds = $request->query->get('userIds', '');
        if (empty($userIds)) {
            return array();
        }
        $userIds = explode(',', rtrim($userIds, ','));
        $userFollowed = $this->getUserService()->findFollowersByFromId($this->getCurrentUser()->getId());
        $userFollowed = ArrayToolkit::index($userFollowed, 'toId');
        $relationships = array();
        foreach ($userIds as $userId) {
            $userFollow = empty($userFollowed[$userId]) ? array() : $userFollowed[$userId];
            $relationships[] = array(
                'userId' => $userId,
                'followed' => empty($userFollow) ? false : true,
            );
        }

        return $relationships;
    }

    public function get(ApiRequest $request, $userId)
    {
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->service('CorporateTrainingBundle:User:UserService');
    }
}
