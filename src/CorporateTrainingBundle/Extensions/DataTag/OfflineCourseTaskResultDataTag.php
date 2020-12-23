<?php

namespace CorporateTrainingBundle\Extensions\DataTag;

use AppBundle\Extensions\DataTag\CourseBaseDataTag;
use AppBundle\Extensions\DataTag\DataTag;

class OfflineCourseTaskResultDataTag extends CourseBaseDataTag implements DataTag
{
    public function getData(array $arguments)
    {
        $offlineCourseTaskResult = $this->getOfflineCourseTaskResultService()->getTaskResultByTaskIdAndUserId($arguments['taskId'], $arguments['userId']);

        return $offlineCourseTaskResult;
    }

    protected function getOfflineCourseTaskResultService()
    {
        return $this->getServiceKernel()->createService('CorporateTrainingBundle:OfflineCourse:TaskService');
    }
}
