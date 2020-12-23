<?php

namespace Biz\CloudPlatform;

use Topxia\Service\Common\ServiceKernel;

class KeyApplier
{
    public function applyKey($user, $edition = 'opensource', $source = 'apply')
    {
        $setting = $this->getSettingService()->get('storage', array());

        if (!empty($setting['cloud_access_key']) && !empty($setting['cloud_secret_key']) && !empty($setting['cloud_key_applied'])) {
            return array(
                'accessKey' => $setting['cloud_access_key'],
                'secretKey' => $setting['cloud_secret_key'],
            );
        }

        $profile = $this->getUserService()->getUserProfile($user['id']);

        $params = array();
        $site = $this->getSettingService()->get('site');

//        $params['siteName'] = empty($site['name']) ? 'EduSoho网络课程' : $site['name'];
        $params['siteName'] = '网络课程';
        $params['siteUrl'] = 'http://'.$_SERVER['HTTP_HOST'];
//        $params['email'] = empty($user['email']) ? '' : $user['email'];
        $params['email'] = '';
//        $params['contact'] = empty($profile['truename']) ? '' : $profile['truename'];
        $params['contact'] = '';
//        $params['qq'] = empty($profile['qq']) ? '' : $profile['qq'];
        $params['qq'] ='';
//        $params['mobile'] = empty($profile['mobile']) ? '' : $profile['mobile'];
        $params['mobile'] ='';
//        $params['edition'] = empty($edition) ? 'opensource' : $edition;
        $params['edition'] = 'opensource';
        $params['source'] = empty($source) ? 'apply' : $source;
        $params['visitorId'] = empty($user['visitorId']) ? '' : $user['visitorId'];

        $url = empty($setting['cloud_api_server']) ? 'http://api.edusoho.net' : rtrim($setting['cloud_api_server'], '/');
        $url = $url.'/v1/keys';

        $response = $this->postRequest($url, $params);

        $key = json_decode($response, true);
        if (empty($key)) {
            return array('error' => '生成Key失败，请检查服务器的网络设置！');
        }

        return $key;
    }

    protected function postRequest($url, $params)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERAGENT, 'EduSoho Install Client 1.0');
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($curl, CURLOPT_TIMEOUT, 20);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($curl, CURLOPT_URL, $url);

        ksort($params);
        $headers[] = 'Content-type: application/json';
        $headers[] = 'Sign: '.md5(json_encode($params));
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }

    protected function getKernel()
    {
        return ServiceKernel::instance();
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User:UserService');
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->createService('System:SettingService');
    }
}
