<?php

namespace CorporateTrainingBundle\Controller\Admin;

use AppBundle\Common\ArrayToolkit;
use CorporateTrainingBundle\Biz\DingTalk\Client\DingTalkClientFactory;
use CorporateTrainingBundle\Biz\DingTalk\Service\DingTalkService;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Component\OAuthClient\OAuthClientFactory;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use AppBundle\Controller\Admin\UserSettingController as BaseController;

class UserSettingController extends BaseController
{
    public function loginConnectAction(Request $request)
    {
        $clients = OAuthClientFactory::clients();
        $default = $this->getDefaultLoginConnect($clients);
        $loginConnect = $this->getSettingService()->get('login_bind', array());
        $loginConnect = array_merge($default, $loginConnect);
        $syncSetting = $this->getSettingService()->get('sync_department_setting', array());

        if ($request->isMethod('POST')) {
            $loginConnect = $request->request->all();
            $loginConnect = ArrayToolkit::trim($loginConnect);
            $syncSetting['enable'] = isset($syncSetting['enable']) ? $syncSetting['enable'] : 0;

            if ('close' == $loginConnect['dingtalkMode'] && !$syncSetting['enable']) {
                $loginConnect['dingtalkweb_enabled'] = 0;
                $loginConnect['dingtalkmob_enabled'] = 0;
            } else {
                $loginConnect['dingtalkweb_enabled'] = 1;
                $loginConnect['dingtalkmob_enabled'] = 1;
            }

            $loginConnect = $this->decideEnabledLoginConnect($loginConnect);
            $this->getSettingService()->set('login_bind', $loginConnect);
            $this->updateWeixinMpFile($loginConnect['weixinmob_mp_secret']);
            $this->getLogService()->info('system', 'update_settings', '更新登录设置', $loginConnect);
        }

        return $this->render('admin/system/login-connect.html.twig', array(
            'loginConnect' => $loginConnect,
            'clients' => $clients,
        ));
    }

    public function syncAccountDockingAction(Request $request)
    {
        $syncSetting = $this->getSettingService()->get('sync_department_setting', array());
        $loginConnect = $this->getSettingService()->get('login_bind', array());
        if ($request->isMethod('POST')) {
            $data = $request->request->all();

            if ('dingtalk' == $data['sync_mode']) {
                $this->setDingTalkSyncMode($data, $loginConnect, $syncSetting);
            } elseif ('closed' == $data['sync_mode']) {
                $this->closeSyncMode($loginConnect, $syncSetting);
            }
            $this->setFlashMessage('success', 'admin.user_setting.message.sync_account_docking');

            $syncSetting = $this->getSettingService()->get('sync_department_setting', array());
            $loginConnect = $this->getSettingService()->get('login_bind', array());
        }

        $syncMode = $this->getSyncMode($syncSetting);
        $host = $this->generateUrl('homepage', array(), UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->render('@CorporateTraining/admin/system/sync-account-docking.html.twig', array(
            'loginConnect' => $loginConnect,
            'syncSetting' => $syncSetting,
            'sync_mode' => $syncMode,
            'host' => $host,
            'ip' => $_SERVER['SERVER_ADDR'],
        ));
    }

    protected function setDingTalkSyncMode($data, $loginConnect, $syncSetting)
    {
        $biz = $this->getBiz();

        $biz['db']->beginTransaction();
        try {
            if (isset($data['dingtalksync_key']) && isset($data['dingtalksync_secret'])) {
                $setting = array(
                    'enable' => 1,
                    'type' => 'dingtalk',
                    'agentId' => $data['dingtalksync_agentId'],
                    'key' => $data['dingtalksync_key'],
                    'secret' => $data['dingtalksync_secret'],
                    'syncTime' => (empty($syncSetting['syncTime'])) ? 0 : $syncSetting['syncTime'],
                    'times' => (empty($syncSetting['times'])) ? 0 : $syncSetting['times'],
                );

                $this->getSettingService()->set('sync_department_setting', $setting);
            }

            $loginConnect['dingtalkweb_key'] = $data['dingtalkweb_key'];
            $loginConnect['dingtalkweb_secret'] = $data['dingtalkweb_secret'];
            $loginConnect['dingtalkmob_key'] = $loginConnect['dingtalkweb_key'];
            $loginConnect['dingtalkmob_secret'] = $loginConnect['dingtalkweb_secret'];
            $loginConnect['dingtalkMode'] = 'login';
            $loginConnect['enabled'] = 1;
            $loginConnect['dingtalkweb_enabled'] = 1;
            $loginConnect['dingtalkmob_enabled'] = 1;
            $this->getSettingService()->set('login_bind', $loginConnect);

            $biz['db']->commit();
        } catch (\Exception $e) {
            $biz['db']->rollback();
            throw $e;
        }
    }

    public function checkDingTalkSyncSettingAction(Request $request)
    {
        $data = $request->request->all();
        if (empty($data['dingtalksync_key']) || empty($data['dingtalksync_secret']) || empty($data['dingtalksync_agentId'])) {
            return $this->createJsonResponse(array('status' => 'error', 'message' => '请先完善配置！'));
        }
        $syncSetting = $this->getSettingService()->get('sync_department_setting', array());
        $syncSetting['key'] = $data['dingtalksync_key'];
        $syncSetting['secret'] = $data['dingtalksync_secret'];
        $syncSetting['agentId'] = $data['dingtalksync_agentId'];
        $this->getSettingService()->set('sync_department_setting', $syncSetting);
        $messageClient = DingTalkClientFactory::create();
        $result = $messageClient->getAccessToken();
        if (empty($result)) {
            $result = array('status' => 'error', 'message' => '钉钉配置错误＊＊具体在系统日志中查看!');
        } else {
            $result = array('status' => 'success', 'message' => $this->trans('admin.user_setting.account_synchronization.setting_success'));
        }

        return $this->createJsonResponse($result);
    }

    protected function closeSyncMode($loginConnect, $syncSetting)
    {
        $biz = $this->getBiz();

        $biz['db']->beginTransaction();
        try {
            $syncSetting['enable'] = 0;
            $this->getSettingService()->set('sync_department_setting', $syncSetting);

            $loginConnect['dingtalkMode'] = 'close';
            $loginConnect['dingtalkweb_enabled'] = 0;
            $loginConnect['dingtalkmob_enabled'] = 0;
            if (empty($loginConnect['weixinweb_enabled']) && empty($loginConnect['weixinmob_enabled'])) {
                $loginConnect['enabled'] = 0;
            }
            $this->getSettingService()->set('login_bind', $loginConnect);
            $this->getSettingService()->delete('dingtalk_notification');

            $biz['db']->commit();
        } catch (\Exception $e) {
            $biz['db']->rollback();
            throw $e;
        }
    }

    private function getSyncMode($syncSetting)
    {
        if (!empty($syncSetting) && $syncSetting['enable']) {
            return $syncMode = 'dingtalk';
        }

        return $syncMode = 'closed';
    }

    private function getDefaultLoginConnect($clients)
    {
        $default = array(
            'login_limit' => 0,
            'enabled' => 0,
            'verify_code' => '',
            'captcha_enabled' => 0,
            'temporary_lock_enabled' => 0,
            'temporary_lock_allowed_times' => 5,
            'ip_temporary_lock_allowed_times' => 20,
            'temporary_lock_minutes' => 20,
        );

        foreach ($clients as $type => $client) {
            $default["{$type}_enabled"] = 0;
            $default["{$type}_key"] = '';
            $default["{$type}_secret"] = '';
            $default["{$type}_set_fill_account"] = 0;
            if ('weixinmob' == $type) {
                $default['weixinmob_mp_secret'] = '';
            }
        }

        return $default;
    }

    private function decideEnabledLoginConnect($loginConnect)
    {
        if (0 == $loginConnect['enabled']) {
            $loginConnect['only_third_party_login'] = 0;
            $loginConnect['weibo_enabled'] = 0;
            $loginConnect['qq_enabled'] = 0;
            $loginConnect['renren_enabled'] = 0;
            $loginConnect['weixinweb_enabled'] = 0;
            $loginConnect['weixinmob_enabled'] = 0;
            $loginConnect['dingtalkweb_enabled'] = 0;
        }
        //新增第三方登陆方式，加入下列列表计算，以便判断是否关闭第三方登陆功能
        $loginConnects = ArrayToolkit::parts($loginConnect, array('weibo_enabled', 'qq_enabled', 'renren_enabled', 'weixinweb_enabled', 'weixinmob_enabled', 'dingtalkweb_enabled'));
        $sum = 0;
        foreach ($loginConnects as $value) {
            $sum += $value;
        }

        if ($sum < 1) {
            if (1 == $loginConnect['enabled']) {
                $this->setFlashMessage('danger', 'admin.user_setting.message.login_connect.error');
            }
            if (0 == $loginConnect['enabled']) {
                $this->setFlashMessage('success', 'admin.user_setting.message.login_connect.success');
            }
            $loginConnect['enabled'] = 0;
        } else {
            $loginConnect['enabled'] = 1;
            $this->setFlashMessage('success', 'admin.user_setting.message.login_connect.success');
        }

        if (isset($loginConnect['dingtalkweb_enabled'])) {
            $loginConnect['dingtalkmob_enabled'] = $loginConnect['dingtalkweb_enabled'];
        }

        if (isset($loginConnect['dingtalkweb_key'])) {
            $loginConnect['dingtalkmob_key'] = $loginConnect['dingtalkweb_key'];
        }

        if (isset($loginConnect['dingtalkweb_secret'])) {
            $loginConnect['dingtalkmob_secret'] = $loginConnect['dingtalkweb_secret'];
        }

        return $loginConnect;
    }

    /**
     * @return DingTalkService
     */
    protected function getDingTalkService()
    {
        return $this->createService('CorporateTrainingBundle:DingTalk:DingTalkService');
    }

    /**
     * @return \LDAPPlugin\Biz\LDAP\Service\Impl\LDAPServiceImpl
     */
    protected function getLdapService()
    {
        return $this->createService('LDAPPlugin:LDAP:LdapService');
    }

    /**
     * @return SchedulerService
     */
    protected function getSchedulerService()
    {
        return $this->createService('Scheduler:SchedulerService');
    }
}
