<?php

namespace CorporateTrainingBundle\Biz\User\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;
use Topxia\Service\Common\ServiceKernel;

class SyncMaxUsersNumberJob extends AbstractJob
{
    public function execute()
    {
        $maxUsersNumber = $this->getUserService()->getMaxUsersNumber();
        $syncUsersNumber = $this->getEduCloudService()->syncMaxUsersNumber();
        if ($maxUsersNumber != $syncUsersNumber) {
            $this->getSettingService()->set('max_users_number', $syncUsersNumber);
        }
    }

    protected function getEduCloudService()
    {
        return $this->getServiceKernel()->createService('CorporateTrainingBundle:CloudPlatform:EduCloudService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('CorporateTrainingBundle:User:UserService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System:SettingService');
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
