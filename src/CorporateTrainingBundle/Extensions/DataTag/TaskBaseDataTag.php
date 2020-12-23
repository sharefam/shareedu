<?php

namespace CorporateTrainingBundle\Extensions\DataTag;

use Biz\Task\Service\TaskService;
use AppBundle\Extensions\DataTag\BaseDataTag;
use AppBundle\Extensions\DataTag\DataTag;

abstract class TaskBaseDataTag extends BaseDataTag implements DataTag
{
    protected function checkUserId(array $arguments)
    {
        if (empty($arguments['userId'])) {
            throw new \InvalidArgumentException($this->getServiceKernel()->trans('userId参数缺失'));
        }
    }

    protected function checkTaskId(array $arguments)
    {
        if (empty($arguments['taskId'])) {
            throw new \InvalidArgumentException($this->getServiceKernel()->trans('taskId参数缺失'));
        }
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->getServiceKernel()->createService('Task:TaskService');
    }

    protected function getTaskResultService()
    {
        return $this->getServiceKernel()->getBiz()->service('Task:TaskResultService');
    }
}
