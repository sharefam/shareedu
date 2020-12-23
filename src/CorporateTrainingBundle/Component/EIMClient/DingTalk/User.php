<?php

namespace CorporateTrainingBundle\Component\EIMClient\DingTalk;

use CorporateTrainingBundle\Component\EIMClient\AbstractUser;

class User extends AbstractUser
{
    protected $accessTokenUrl = 'https://oapi.dingtalk.com/gettoken';
    protected $userListUrl = 'https://oapi.dingtalk.com/user/list';
    protected $userSimpleListUrl = 'https://oapi.dingtalk.com/user/simplelist';
    protected $userUrl = 'https://oapi.dingtalk.com/user/get';
    protected $userUrlByUnionId = 'https://oapi.dingtalk.com/user/getUseridByUnionid';
    protected $userCreateUrl = 'https://oapi.dingtalk.com/user/create?';
    protected $userUpdateUrl = 'https://oapi.dingtalk.com/user/update?';
    protected $userDeleteUrl = 'https://oapi.dingtalk.com/user/delete';

    protected $contentType = array('Content-type: application/json');

    public function __construct($config)
    {
        parent::__construct($config);
    }

    public function getUserIdByUnionId($unionId)
    {
        $params = array(
            'access_token' => $this->token,
            'unionid' => $unionId,
        );

        $user = $this->getRequest($this->userUrlByUnionId, $params);
        $user = json_decode($user, true);

        if (!$user || $user['errcode'] != 0) {
            return null;
        }

        return $user['userid'];
    }

    public function postRequest($url, $params)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERAGENT, $this->agent);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->contentType);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        curl_setopt($curl, CURLOPT_URL, $url);

        // curl_setopt($curl, CURLINFO_HEADER_OUT, TRUE );

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }
}
