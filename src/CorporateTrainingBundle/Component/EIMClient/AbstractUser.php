<?php

namespace CorporateTrainingBundle\Component\EIMClient;

abstract class AbstractUser
{
    protected $agent = 'CorporateTraining Enterprise Instant Messaging Client 1.0';

    protected $connectTimeout = 30;

    protected $timeout = 30;

    protected $token;

    public function __construct($config)
    {
        $params = array(
            'corpid' => $config['key'],
            'corpsecret' => $config['secret'],
        );

        $token = $this->getToken($params);
        $this->token = $token;
    }

    public function create($user)
    {
        $params = array('access_token' => $this->token);
        $result = $this->postRequest($this->userCreateUrl.http_build_query($params), json_encode($user));

        return json_decode($result, true);
    }

    public function update($user)
    {
        $params = array('access_token' => $this->token);
        $result = $this->postRequest($this->userUpdateUrl.http_build_query($params), json_encode($user));

        return json_decode($result, true);
    }

    public function delete($id)
    {
        $params = array(
            'access_token' => $this->token,
            'id' => $id,
        );

        $result = $this->getRequest($this->userDeleteUrl, $params);

        return json_decode($result, true);
    }

    public function get($id)
    {
        $params = array(
            'access_token' => $this->token,
            'userid' => $id,
        );

        $user = $this->getRequest($this->userUrl, $params);

        return json_decode($user, true);
    }

    public function simplelists($departmentId)
    {
        $params = array(
            'access_token' => $this->token,
            'department_id' => $departmentId,
        );
        $users = $this->getRequest($this->userSimpleListUrl, $params);
        $users = json_decode($users, true);

        return $users['userlist'];
    }

    public function lists($departmentId)
    {
        $params = array(
            'access_token' => $this->token,
            'department_id' => $departmentId,
        );
        $users = $this->getRequest($this->userListUrl, $params);
        $users = json_decode($users, true);

        return $users['userlist'];
    }

    protected function getToken($params)
    {
        $token = $this->getRequest($this->accessTokenUrl, $params);
        $token = json_decode($token, true);

        return $token['access_token'];
    }

    /**
     * HTTP POST.
     *
     * @param string $url    要请求的url地址
     * @param array  $params 请求的参数
     *
     * @return string
     */
    public function postRequest($url, $params)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERAGENT, $this->agent);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
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

    /**
     * HTTP GET.
     *
     * @param string $url    要请求的url地址
     * @param array  $params 请求的参数
     *
     * @return string
     */
    public function getRequest($url, $params)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERAGENT, $this->agent);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);

        $url = $url.'?'.http_build_query($params);
        curl_setopt($curl, CURLOPT_URL, $url);

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }
}
