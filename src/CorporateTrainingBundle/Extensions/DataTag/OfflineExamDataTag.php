<?php

namespace CorporateTrainingBundle\Extensions\DataTag;

use AppBundle\Extensions\DataTag\CourseBaseDataTag;
use AppBundle\Extensions\DataTag\DataTag;

class OfflineExamDataTag extends CourseBaseDataTag implements DataTag
{
    public function getData(array $arguments)
    {
        $offlineExam = $this->getOfflineExamService()->getOfflineExam($arguments['id']);

        return $offlineExam;
    }

    protected function getOfflineExamService()
    {
        return $this->getServiceKernel()->createService('CorporateTrainingBundle:OfflineExam:OfflineExamService');
    }
}
