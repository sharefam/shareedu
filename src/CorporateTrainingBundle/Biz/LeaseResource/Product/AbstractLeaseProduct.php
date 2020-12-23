<?php

namespace CorporateTrainingBundle\Biz\LeaseResource\Product;

use CorporateTrainingBundle\Biz\LeaseResource\Client\ResourcePlatformApi;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

abstract class AbstractLeaseProduct
{
    protected $biz = null;
    protected $api = null;

    public function __construct($biz)
    {
        $this->biz = $biz;
    }

    abstract public function createLeaseProduct($resourceCode);

    protected function getApiClient()
    {
        if (!$this->api) {
            $storage = $this->getSettingService()->get('storage', array());
            $developer = $this->getSettingService()->get('developer', array());

            $logger = new Logger('CloudAPI');
            $logger->pushHandler(new StreamHandler($this->biz['log_directory'].'/resource-platform-api.log', Logger::DEBUG));

            $this->api = new ResourcePlatformApi(array(
                'accessKey' => empty($storage['cloud_access_key']) ? '' : $storage['cloud_access_key'],
                'secretKey' => empty($storage['cloud_secret_key']) ? '' : $storage['cloud_secret_key'],
                'apiUrl' => empty($developer['resource_platform_server']) ? '' : rtrim($developer['resource_platform_server'], '/'),
                'debug' => empty($developer['debug']) ? false : true,
            ));

            $this->api->setLogger($logger);
        }

        return $this->api;
    }

    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }
}
