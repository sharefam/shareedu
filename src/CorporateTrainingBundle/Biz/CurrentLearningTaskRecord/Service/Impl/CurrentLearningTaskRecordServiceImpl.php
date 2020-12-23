<?php

namespace CorporateTrainingBundle\Biz\CurrentLearningTaskRecord\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use CorporateTrainingBundle\Biz\CurrentLearningTaskRecord\Service\CurrentLearningTaskRecordService;

class CurrentLearningTaskRecordServiceImpl extends BaseService implements CurrentLearningTaskRecordService
{
    public function getCurrentLearningTaskRecord($id)
    {
        return $this->getCurrentLearningTaskRecordDao()->get($id);
    }

    public function getCurrentLearningTaskRecordByUserId($userId)
    {
        return $this->getCurrentLearningTaskRecordDao()->getByUserId($userId);
    }

    public function createCurrentLearningTaskRecord($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('userId', 'taskId', 'startTime', 'lastTriggerTime'))) {
            throw $this->createInvalidArgumentException('parameters is invalid');
        }

        return $this->getCurrentLearningTaskRecordDao()->create($fields);
    }

    public function getCurrentLearningTaskRecordForUpdate($id)
    {
        return $this->getCurrentLearningTaskRecordDao()->getForUpdate($id);
    }

    public function updateCurrentLearningTaskRecord($id, $fields)
    {
        return $this->getCurrentLearningTaskRecordDao()->update($id, $fields);
    }

    public function deleteCurrentLearningTaskRecord($id)
    {
        return $this->getCurrentLearningTaskRecordDao()->delete($id);
    }

    public function searchCurrentLearningTaskRecord($conditions, $orderBy, $start, $limit)
    {
        return $this->getCurrentLearningTaskRecordDao()->search($conditions, $orderBy, $start, $limit);
    }

    /**
     * @return \CorporateTrainingBundle\Biz\CurrentLearningTaskRecord\Dao\Impl\CurrentLearningTaskRecordDaoImpl
     */
    protected function getCurrentLearningTaskRecordDao()
    {
        return $this->createDao('CorporateTrainingBundle:CurrentLearningTaskRecord:CurrentLearningTaskRecordDao');
    }
}
