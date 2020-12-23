<?php

namespace CorporateTrainingBundle\Extensions\DataTag;

use AppBundle\Extensions\DataTag\BaseDataTag;
use AppBundle\Extensions\DataTag\DataTag;
use CorporateTrainingBundle\Biz\OfflineActivity\Service\EnrollmentRecordService;

class OfflineActivitySubmittedStudentNumDataTag extends BaseDataTag implements DataTag
{
    public function getData(array $arguments)
    {
        return $this->getRecordService()->calculateOfflineActivitySubmittedStudentNum($arguments['offlineActivityId']);
    }

    /**
     * @return EnrollmentRecordService
     */
    protected function getRecordService()
    {
        return $this->getServiceKernel()->getBiz()->service('CorporateTrainingBundle:OfflineActivity:EnrollmentRecordService');
    }
}
