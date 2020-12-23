<?php

namespace AppBundle\Component\OAuthClient;

use InvalidArgumentException;
use Topxia\Service\Common\ServiceKernel;

class OAuthClientFactory
{
    /**
     * 创建OAuthClient实例.
     *
     * @param string $type   Client的类型
     * @param array  $config 必需包含key, secret两个参数
     *
     * @return AbstractOauthClient
     */
    public static function create($type, array $config)
    {
        if (!array_key_exists('key', $config) || !array_key_exists('secret', $config)) {
            throw new InvalidArgumentException('参数中必需包含key, secret两个为key的值');
        }

        $clients = self::clients();

        if (!array_key_exists($type, $clients)) {
            throw new InvalidArgumentException('参数不正确'.$type);
        }

        $class = $clients[$type]['class'];

        return new $class($config);
    }

    public static function clients()
    {
        $clients = array(
            'weibo' => array(
                'name' => '微博帐号',
                'admin_name' => '微博登录接口',
                'class' => 'AppBundle\Component\OAuthClient\WeiboOAuthClient',
                'icon_class' => 'weibo',
                'icon_img' => '',
                'large_icon_img' => 'assets/img/social/weibo.png',
                'key_setting_label' => 'App Key',
                'secret_setting_label' => 'App Secret',
                'apply_url' => 'http://open.weibo.com/authentication/',
            ),
            'qq' => array(
                'name' => 'QQ帐号',
                'admin_name' => 'QQ登录接口',
                'class' => 'AppBundle\Component\OAuthClient\QqOAuthClient',
                'icon_class' => 'qq',
                'icon_img' => '',
                'large_icon_img' => 'assets/img/social/qq.png',
                'key_setting_label' => 'App ID',
                'secret_setting_label' => 'App Key',
                'apply_url' => 'http://connect.qq.com/',
            ),
            'weixinweb' => array(
                'name' => self::getServiceKernel()->trans('admin.user.settings.oauth.wechat_web_login_interface'),
                'admin_name' => self::getServiceKernel()->trans('admin.user.settings.oauth.wechat_web_login_interface'),
                'class' => 'AppBundle\Component\OAuthClient\WeixinwebOAuthClient',
                'icon_class' => 'weixin',
                'icon_img' => '',
                'large_icon_img' => 'assets/img/social/weixin.png',
                'key_setting_label' => 'App ID',
                'secret_setting_label' => 'App Secret',
                'apply_url' => 'https://open.weixin.qq.com/cgi-bin/frame?t=home/web_tmpl&lang=zh_CN',
            ),
            'weixinmob' => array(
                'name' => self::getServiceKernel()->trans('admin.user.settings.oauth.wechat_sharing_login_interface'),
                'admin_name' => self::getServiceKernel()->trans('admin.user.settings.oauth.wechat_sharing_login_interface'),
                'class' => 'AppBundle\Component\OAuthClient\WeixinmobOAuthClient',
                'icon_class' => '',
                'icon_img' => '',
                'large_icon_img' => '',
                'key_setting_label' => 'App ID',
                'secret_setting_label' => 'App Secret',
                'mp_secret_setting_label' => self::getServiceKernel()->trans('admin.payment_setting.wxpay_secret'),
                'apply_url' => 'https://mp.weixin.qq.com/cgi-bin/readtemplate?t=register/step1_tmpl&lang=zh_CN',
            ),
            'dingtalkweb' => array(
                'name' => '钉钉网页登录接口',
                'admin_name' => '钉钉网页登录接口',
                'class' => 'AppBundle\Component\OAuthClient\DingtalkwebOAuthClient',
                'icon_class' => 'dingding',
                'icon_img' => '',
                'large_icon_img' => 'assets/img/social/dingtalk.png',
                'key_setting_label' => 'App ID',
                'secret_setting_label' => 'App Secret',
                'apply_url' => 'https://oapi.dingtalk.com/connect/qrconnect?appid=APPID&response_type=code&scope=snsapi_login&state=STATE&redirect_uri=REDIRECT_URI',
            ),
            'dingtalkmob' => array(
                'name' => '钉钉内分享登录接口',
                'admin_name' => '钉钉内分享登录接口',
                'class' => 'AppBundle\Component\OAuthClient\DingtalkmobOAuthClient',
                'icon_class' => '',
                'icon_img' => '',
                'large_icon_img' => '',
                'key_setting_label' => 'App ID',
                'secret_setting_label' => 'App Secret',
                'apply_url' => 'https://oapi.dingtalk.com/connect/oauth2/sns_authorize?appid=APPID&response_type=code&scope=snsapi_login&state=STATE&redirect_uri=REDIRECT_URI',
            ),
        );

        if (self::getServiceKernel()->hasParameter('oauth2_clients')) {
            $extras = self::getServiceKernel()->getParameter('oauth2_clients');
            $clients = array_merge($clients, $extras);
        }

        return $clients;
    }

    protected static function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
