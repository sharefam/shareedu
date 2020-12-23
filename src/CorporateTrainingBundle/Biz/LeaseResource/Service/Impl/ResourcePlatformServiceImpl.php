<?php

namespace CorporateTrainingBundle\Biz\LeaseResource\Service\Impl;

use Biz\BaseService;
use CorporateTrainingBundle\Biz\LeaseResource\Service\ResourcePlatformService;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use CorporateTrainingBundle\Biz\LeaseResource\Client\ResourcePlatformApi;

class ResourcePlatformServiceImpl extends BaseService implements ResourcePlatformService
{
    protected $api = null;

    public function leaseProductInfo($resourceType, $resourceCode)
    {
        $leaseProduct = $this->getLeaseProduct($resourceType);

        return $leaseProduct->createLeaseProduct($resourceCode);
    }

    public function getResourceToken($globalId, $uri, $leaseResourceType, $leaseResourceId, $params = array())
    {
        return $this->getApiClient()->getResourceToken($globalId, $uri, $leaseResourceType, $leaseResourceId, $params);
    }

    private function getLeaseProduct($resourceType)
    {
        if ($this->biz['lease_product.'.$resourceType]) {
            return $this->biz['lease_product.'.$resourceType];
        }

        return null;
    }

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
