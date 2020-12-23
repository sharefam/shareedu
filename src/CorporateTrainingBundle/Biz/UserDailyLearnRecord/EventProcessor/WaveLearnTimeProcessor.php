<?php

namespace CorporateTrainingBundle\Biz\UserDailyLearnRecord\EventProcessor;

class WaveLearnTimeProcessor extends AbstractEventProcessor
{
    public function waveRecord($recordId, $fields)
    {
        if (!empty($fields['learnTime'])) {
            return $this->getUserDailyLearnRecordService()->waveLearnTimeById($recordId, $fields['learnTime']);
        }
    }
}
