<?php

namespace CorporateTrainingBundle\Biz\CloudPlatform\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\CloudPlatform\Service\Impl\AppServiceImpl as BaseService;
use CorporateTrainingBundle\System;

class AppServiceImpl extends BaseService
{
    public function getMainVersion()
    {
        $app = $this->getAppDao()->getByCode('TRAININGMAIN');

        return $app['version'];
    }

    public function checkAppUpgrades()
    {
        $mainApp = $this->getAppDao()->getByCode('TRAININGMAIN');

        if (empty($mainApp)) {
            $this->addEduSohoMainApp();
        }

        $apps = $this->findApps(0, self::MAX_APP_COUNT);

        $args = array();

        foreach ($apps as $app) {
            $args[$app['code']] = $app['version'];
        }

        $lastCheck = intval($this->getSettingService()->get('_app_last_check'));

        if (empty($lastCheck) || ((time() - $lastCheck) > 86400)) {
            $this->getSettingService()->set('_app_last_check', time());
            $extInfos = array();
        } else {
            $extInfos = array('_t' => (string) time());
        }

        return $this->createAppClient()->checkUpgradePackages($args, $extInfos);
    }

    protected function addEduSohoMainApp()
    {
        $app = array(
            'code' => 'TRAININGMAIN',
            'name' => '内训版主系统',
            'description' => '内训版主系统',
            'icon' => '',
            'version' => System::CT_VERSION,
            'fromVersion' => '0.0.0',
            'developerId' => 1,
            'developerName' => 'EduSoho官方',
            'installedTime' => time(),
            'updatedTime' => time(),
        );
        $this->getAppDao()->create($app);
    }

    protected function getPackageRootDirectory($package, $packageDir)
    {
        if ($package['product']['code'] == 'TRAININGMAIN') {
            return $this->getSystemRootDirectory();
        }

        if (file_exists($packageDir.'/ThemeApp')) {
            return realpath($this->biz['theme.directory']);
        }

        return realpath($this->biz['plugin.directory']);
    }

    protected function checkPluginDepend($package)
    {
        if ($package['product']['code'] != 'TRAININGMAIN') {
            return array();
        }
        $count = $this->getAppDao()->countApps();
        $apps = $this->getAppDao()->find(0, $count);
        $apps = ArrayToolkit::index($apps, 'code');
        $systemVersion = $apps['TRAININGMAIN']['version'];
        unset($apps['TRAININGMAIN']);

        $unsupportApps = array_filter($apps, function ($app) use ($systemVersion) {
            return $app['edusohoMaxVersion'] != 'up' && version_compare($app['edusohoMaxVersion'], $systemVersion, '<=');
        });

        return array_map(function ($app) use ($systemVersion) {
            return "{$app['name']}支持的最大版本为{$app['edusohoMaxVersion']},您需要升级该插件";
        }, $unsupportApps);
    }
}
