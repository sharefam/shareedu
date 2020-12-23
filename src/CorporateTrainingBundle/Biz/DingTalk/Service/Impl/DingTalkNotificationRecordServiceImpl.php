<?php

namespace CorporateTrainingBundle\Biz\DingTalk\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use CorporateTrainingBundle\Biz\DingTalk\Dao\DingTalkNotificationRecordDao;
use CorporateTrainingBundle\Biz\DingTalk\Service\DingTalkNotificationRecordService;

class DingTalkNotificationRecordServiceImpl extends BaseService implements DingTalkNotificationRecordService
{
    public function createRecord($record)
    {
        if (!ArrayToolkit::requireds($record, array('taskId', 'data', 'agentId', 'targetType', 'targetId', 'batch'))) {
            throw $this->createInvalidArgumentException('Lack of required fields');
        }
        $record = ArrayToolkit::parts($record, array('taskId', 'data', 'agentId', 'targetType', 'targetId', 'batch'));

        return $this->getDingTalkNotificationRecordDao()->create($record);
    }

    /**
     * @return DingTalkNotificationRecordDao
     */
    protected function getDingTalkNotificationRecordDao()
    {
        return $this->createDao('CorporateTrainingBundle:DingTalk:DingTalkNotificationRecordDao');
    }
}
