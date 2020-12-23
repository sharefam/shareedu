<?php

namespace CorporateTrainingBundle\Biz\CloudPlatform\Service\Impl;

use Biz\CloudPlatform\CloudAPIFactory;
use Biz\CloudPlatform\Service\Impl\EduCloudServiceImpl as BaseServiceImpl;
use CorporateTrainingBundle\Biz\CloudPlatform\Service\EduCloudService;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Topxia\Service\Common\ServiceKernel;

class EduCloudServiceImpl extends BaseServiceImpl implements EduCloudService
{
    public function syncMaxUsersNumber()
    {
        try {
            $api = CloudAPIFactory::create('root');
            $result = $api->get('/me/max_users_number');
        } catch (\RuntimeException $e) {
            $logger = new Logger('CloudAPI');
            $logger->pushHandler(new StreamHandler(ServiceKernel::instance()->getParameter('kernel.logs_dir').'/cloud-api.log', Logger::DEBUG));
            $logger->addInfo($e->getMessage());
        }

        return isset($result['error']) ? 0 : $result;
    }
}
