<?php

namespace AppBundle\Component\OAuthClient;

use CorporateTrainingBundle\Biz\DingTalk\Client\DingTalkClientFactory;
use Topxia\Service\Common\ServiceKernel;

class DingtalkmobOAuthClient extends AbstractOAuthClient
{
    const USERINFO_URL = 'https://oapi.dingtalk.com/sns/getuserinfo_bycode';
    const AUTHORIZE_URL = 'https://oapi.dingtalk.com/connect/oauth2/sns_authorize?';
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
        $params['scope'] = 'snsapi_auth';

        return self::AUTHORIZE_URL.http_build_query($params);
    }

    public function getAccessToken($code, $callbackUrl)
    {
        $userInfo = $this->getUserInfo($code);
        $userInfo['userId'] = $userInfo['id'];

        return $userInfo;
    }

    public function getUserInfo($code)
    {
        $c = new DingTalkClient(DingTalkConstant::$CALL_TYPE_OAPI, DingTalkConstant::$METHOD_POST, DingTalkConstant::$FORMAT_JSON);
        $req = new OapiSnsGetuserinfoBycodeRequest();
        $req->setTmpAuthCode($code);
        $resp = $c->executeWithAccessKey($req, self::USERINFO_URL, $this->config['key'], $this->config['secret']);
        $userInfo = $this->object_to_array($resp);

        return $this->convertUserInfo($userInfo);
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

    protected function object_to_array($obj)
    {
        $obj = (array) $obj;
        foreach ($obj as $k => $v) {
            if ('resource' == gettype($v)) {
                return;
            }
            if ('object' == gettype($v) || 'array' == gettype($v)) {
                $obj[$k] = (array) $this->object_to_array($v);
            }
        }

        return $obj;
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->createService('System:SettingService');
    }
}
