<?php

namespace ApiBundle\Api\Resource\User;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\Annotation\ResponseFilter;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;

class User extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     * @ResponseFilter(class="ApiBundle\Api\Resource\User\UserFilter", mode="simple")
     */
    public function get(ApiRequest $request, $identify)
    {
        $identifyType = $request->query->get('identifyType', 'id');

        $user = null;
        switch ($identifyType) {
            case 'id':
                $user = $this->getUserService()->getUser($identify);
                break;
            case 'email':
                $user = $this->getUserService()->getUserByEmail($identify);
                break;
            case 'mobile':
                $user = $this->getUserService()->getUserByVerifiedMobile($identify);
                break;
            case 'nickname':
                $user = $this->getUserService()->getUserByNickname($identify);
                break;
            case 'token':
                $user = $this->getUserService()->getUserByUUID($identify);
                break;
            default:
                break;
        }

        return $user;
    }

    public function search(ApiRequest $request)
    {
        $fields = $request->query->all();
        $conditions = array(
            'roles' => '',
            'keywordType' => '',
            'keyword' => '',
            'keywordUserType' => '',
            'noType' => 'system',
        );

        $conditions = array_merge($conditions, $fields);
        $sort = $this->getSort($request);
        $sort = $sort ? $sort : array('id' => 'DESC');
        $total = $this->getUserService()->countUsers($conditions);
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $users = $this->getUserService()->searchUsers(
            $conditions,
            $sort,
            $offset,
            $limit
        );

        return $this->makePagingObject($users, $total, $offset, $limit);
    }

    /**
     * @return \Biz\User\Service\UserService
     */
    protected function getUserService()
    {
        return $this->service('User:UserService');
    }
}
