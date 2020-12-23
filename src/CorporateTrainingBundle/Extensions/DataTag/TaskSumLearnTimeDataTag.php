<?php

namespace CorporateTrainingBundle\Extensions\DataTag;

use AppBundle\Extensions\DataTag\DataTag;
use CorporateTrainingBundle\Extensions\DataTag\TaskBaseDataTag as BaseDataTag;

class TaskSumLearnTimeDataTag extends BaseDataTag implements DataTag
{
    public function getData(array $arguments)
    {
        $this->checkUserId($arguments);
        $this->checkTaskId($arguments);

        return $this->getTaskResultService()->sumLearnTimeByTaskIdAndUserId($arguments['taskId'], $arguments['userId']);
    }
}
