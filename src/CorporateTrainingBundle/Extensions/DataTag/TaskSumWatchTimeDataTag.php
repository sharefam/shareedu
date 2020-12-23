<?php

namespace CorporateTrainingBundle\Extensions\DataTag;

use AppBundle\Extensions\DataTag\DataTag;
use CorporateTrainingBundle\Extensions\DataTag\TaskBaseDataTag as BaseDataTag;

class TaskSumWatchTimeDataTag extends BaseDataTag implements DataTag
{
    public function getData(array $arguments)
    {
        $this->checkUserId($arguments);
        $this->checkTaskId($arguments);

        return $this->getTaskResultService()->sumWatchTimeByTaskIdAndUserId($arguments['taskId'], $arguments['userId']);
    }
}
