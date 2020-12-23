<?php

namespace CorporateTrainingBundle\Extensions\DataTag;

use AppBundle\Extensions\DataTag\BaseDataTag;
use AppBundle\Extensions\DataTag\DataTag;
use CorporateTrainingBundle\Biz\OfflineActivity\Service\OfflineActivityService;

class UserApplyStatusDataTag extends BaseDataTag implements DataTag
{
    public function getData(array $arguments)
    {
        return $this->getOfflineActivityService()->getUserApplyStatus(
            $arguments['offlineActivityId'],
            $arguments['userId']
        );
    }

    /**
     * @return OfflineActivityService
     */
    protected function getOfflineActivityService()
    {
        return $this->getServiceKernel()->getBiz()->service(
            'CorporateTrainingBundle:OfflineActivity:OfflineActivityService'
        );
    }
}
