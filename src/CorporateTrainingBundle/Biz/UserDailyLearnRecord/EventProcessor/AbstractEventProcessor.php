<?php

namespace CorporateTrainingBundle\Biz\UserDailyLearnRecord\EventProcessor;

use Codeages\Biz\Framework\Context\Biz;
use CorporateTrainingBundle\Biz\UserDailyLearnRecord\Service\UserDailyLearnRecordService;

abstract class AbstractEventProcessor
{
    protected $biz;

    abstract public function waveRecord($recordId, $fields);

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    public function process($fields)
    {
        if (empty($fields['userId']) || empty($fields['courseId'])) {
            return;
        }

        $record = $this->getUserDailyLearnRecordService()->getTodayRecordByUserIdAndCourseId(
            $fields['userId'],
            $fields['courseId']
        );

        if (empty($record)) {
            $this->getUserDailyLearnRecordService()->createCurrentDateDailyRecord($fields);
        }

        $this->waveRecord($record['id'], $fields);
    }

    /**
     * @return UserDailyLearnRecordService
     */
    protected function getUserDailyLearnRecordService()
    {
        return $this->createService('CorporateTrainingBundle:UserDailyLearnRecord:UserDailyLearnRecordService');
    }

    protected function createService($alias)
    {
        return $this->biz->service($alias);
    }
}
