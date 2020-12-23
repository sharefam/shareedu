<?php

namespace AppBundle\Component\OAuthClient;

use CorporateTrainingBundle\Biz\DingTalk\Client\DingTalkClientFactory;
use Topxia\Service\Common\ServiceKernel;

class DingtalkwebOAuthClient extends AbstractOAuthClient
{
    const USERINFO_URL = 'https://oapi.dingtalk.com/sns/getuserinfo';
    const AUTHORIZE_URL = 'https://oapi.dingtalk.com/connect/qrconnect?';
    const ACCESS_TOKEN_URL = 'https://oapi.dingtalk.com/sns/gettoken';
    const PERSISTENT_CODE_URL = 'https://oapi.dingtalk.com/sns/get_persistent_code?';
    const SNS_TOKEN_URL = 'https://oapi.dingtalk.com/sns/get_sns_token?';

    protected $contentType = array('Content-type: application/json');

    public function getAuthorizeUrl($callbackUrl)
    {
        $params = array();
        $params['appid'] = $this->config['key'];
        $params['response_type'] = 'code';
        $params['redirect_uri'] = $callbackUrl;
        $params['scope'] = 'snsapi_login';

        return self::AUTHORIZE_URL.http_build_query($params);
    }

    public function getAccessToken($code, $callbackUrl)
    {
        $params = array(
            'appid' => $this->config['key'],
            'appsecret' => $this->config['secret'],
        );

        $result = $this->getRequest(self::ACCESS_TOKEN_URL, $params);
        $result = json_decode($result, true);

        $urlField = array(
            'access_token' => $result['access_token'],
        );

        $persistentCode = $this->postRequest(self::PERSISTENT_CODE_URL.http_build_query($urlField), json_encode(array('tmp_auth_code' => $code)));
        $persistentCode = json_decode($persistentCode, true);

        $params = array(
            'openid' => $persistentCode['openid'],
            'persistent_code' => $persistentCode['persistent_code'],
        );

        $snsToken = $this->postRequest(self::SNS_TOKEN_URL.http_build_query($urlField), json_encode($params));
        $snsToken = json_decode($snsToken, true);

        $userInfo = $this->getUserInfo($snsToken);

        return array(
            'userId' => $userInfo['id'],
            'name' => $userInfo['name'],
            'dingtalkUnionid' => $userInfo['dingtalkUnionid'],
            'dingtalkUserid' => $userInfo['dingtalkUserid'],
            'avatar' => $userInfo['avatar'],
            'expiredTime' => $snsToken['expires_in'],
            'access_token' => $result['access_token'],
            'sns_token' => $snsToken['sns_token'],
            'openid' => $persistentCode['openid'],
        );
    }

    public function getUserInfo($token)
    {
        $params = array(
            'sns_token' => $token['sns_token'],
        );

        $result = $this->getRequest(self::USERINFO_URL, $params);

        $info = json_decode($result, true);

        return $this->convertUserInfo($info);
    }

    private function convertUserInfo($info)
    {
        if (empty($info)) {
            return;
        }
        $dingTalkUserInfo = null;
        $syncSetting = $this->getSettingService()->get('sync_department_setting', array());
        if (!empty($syncSetting['key']) && !empty($syncSetting['secret'])) {
            $dingtalkClient = DingTalkClientFactory::create();
            $dingTalkUserInfo = $dingtalkClient->getUserInfoByUnionid($info['user_info']['unionid']);
        }
        $userInfo = array();
        $userInfo['id'] = $info['user_info']['unionid'];
        $userInfo['name'] = $info['user_info']['nick'];
        $userInfo['dingtalkUnionid'] = $info['user_info']['unionid'];
        $userInfo['dingtalkUserid'] = empty($dingTalkUserInfo['userid']) ? '' : $dingTalkUserInfo['userid'];
        $userInfo['avatar'] = empty($dingTalkUserInfo['avatar']) ? '' : $dingTalkUserInfo['avatar'];

        return $userInfo;
    }

    public function postRequest($url, $params)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERAGENT, $this->userAgent);
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

    protected function getSettingService()
    {
        return ServiceKernel::instance()->createService('System:SettingService');
    }
}
