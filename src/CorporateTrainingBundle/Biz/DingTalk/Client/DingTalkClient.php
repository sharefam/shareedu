<?php

namespace CorporateTrainingBundle\Biz\DingTalk\Client;

use Biz\System\Service\SettingService;
use Topxia\Service\Common\ServiceKernel;

class DingTalkClient
{
    /**
     * unionid 获取 userid  method:get
     */
    const GET_USERID_BY_UNIONID_URL = '/user/getUseridByUnionid';

    /**
     * 通过userid 获取 用户详细信息  method:get
     */
    const GET_USERINFO_URL = '/user/get';

    /**
     * 获取access_token method:get
     */
    const ACCESS_TOKEN_URL = '/gettoken';

    /**
     * 发送工作通知消息 method:post
     */
    const ASYNCSEND_URL = '/topapi/message/corpconversation/asyncsend_v2';

    /**
     * 获取部门列表 method:get
     */
    const DEPARTMENT_URL = '/department/list';

    protected static $requestApi = null;

    protected static $appkey = '';

    protected static $appsecret = '';

    protected static $agentId = '';

    public function __construct()
    {
        if (empty(self::$appkey) || empty(self::$appsecret)) {
            $syncSetting = $this->getSettingService()->get('sync_department_setting', array());
            self::$appkey = $syncSetting['key'];
            self::$appsecret = $syncSetting['secret'];
            self::$agentId = empty($syncSetting['agentId']) ? '' : $syncSetting['agentId'];
        }

        if (empty(self::$requestApi)) {
            self::$requestApi = new DingTalkApi();
        }

        return self::$requestApi;
    }

    /**
     * @return string
     */
    public function getAccessToken()
    {
        $params['appkey'] = self::$appkey;
        $params['appsecret'] = self::$appsecret;
        $response = self::$requestApi->get(self::ACCESS_TOKEN_URL, $params);
        if (empty($response['access_token'])) {
            $this->getLogService()->error('dingtalk', 'dingtalk_check_setting', json_encode($response));
        }

        return empty($response['access_token']) ? '' : $response['access_token'];
    }

    /**
     * @return array
     */
    public function getDingTalkDepartmentList()
    {
        $params['access_token'] = urlencode($this->getAccessToken());
        $response = self::$requestApi->get(self::DEPARTMENT_URL, $params);

        return !empty($response['errcode']) ? array() : $response;
    }

    /**
     * @param $userid
     *
     * @return array
     */
    public function getUserInfoByUserid($userid)
    {
        if (empty($userid)) {
            return null;
        }
        $params['access_token'] = urlencode($this->getAccessToken());
        $params['userid'] = $userid;
        $response = self::$requestApi->get(self::GET_USERINFO_URL, $params);

        return !empty($response['errcode']) ? array() : $response;
    }

    /**
     * @param $unionid
     *
     * @return string
     */
    public function getUserIdByUnionid($unionid)
    {
        if (empty($unionid)) {
            return null;
        }
        $params['access_token'] = urlencode($this->getAccessToken());
        $params['unionid'] = $unionid;
        $response = self::$requestApi->get(self::GET_USERID_BY_UNIONID_URL, $params);

        return empty($response['userid']) ? '' : $response['userid'];
    }

    /**
     * @param $userids
     * @param $content
     *
     * @return array|bool
     */
    public function sendMessage($userids, $content)
    {
        if (empty($userids)) {
            return true;
        }
        $params['access_token'] = urlencode($this->getAccessToken());
        $params['agent_id'] = self::$agentId;
        $params['userid_list'] = implode(',', array_values($userids));
        $params['msg'] = json_encode($content);

        return self::$requestApi->post(self::ASYNCSEND_URL, $params);
    }

    /**
     * @param $unionid
     *
     * @return array
     */
    public function getUserInfoByUnionid($unionid)
    {
        $userid = $this->getUserIdByUnionid($unionid);

        if (empty($userid)) {
            return null;
        }

        return $this->getUserInfoByUserid($userid);
    }

    /**
     * @return SettingService
     */
    private function getSettingService()
    {
        return ServiceKernel::instance()->createService('System:SettingService');
    }

    protected function getLogService()
    {
        return ServiceKernel::instance()->createService('System:LogService');
    }

    /**
     * 仅供单元测试使用，正常业务严禁使用
     *
     * @param $client
     */
    public function setCloudApi($client)
    {
        self::$requestApi = $client;
    }
}
