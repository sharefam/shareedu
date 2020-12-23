<?php

namespace CorporateTrainingBundle\Biz\DingTalk\Client;

use Biz\System\Service\SettingService;
use Topxia\Service\Common\ServiceKernel;

class DingTalkClientFactory
{
    /**
     * 创建EIM Message Client实例.
     *
     * @param string $type   Client的类型
     * @param array  $config 必需包含key, secret两个参数
     *
     * @return EIM Message Client
     */
    private static $DingTalkClient;

    public static function create()
    {
        $settingService = ServiceKernel::instance()->createService('System:SettingService');
        $syncSetting = $settingService->get('sync_department_setting', array());

        if (empty($syncSetting['key']) || empty($syncSetting['secret'])) {
            throw new \AppBundle\Common\Exception\InvalidArgumentException('钉钉同步配置不正确');
        }

        if (empty(self::$DingTalkClient)) {
            self::$DingTalkClient = new DingTalkClient();
        }

        return self::$DingTalkClient;
    }

    /**
     * 仅给单元测试mock用。
     */
    public function setClass($class)
    {
        self::$DingTalkClient = $class;
    }
}
