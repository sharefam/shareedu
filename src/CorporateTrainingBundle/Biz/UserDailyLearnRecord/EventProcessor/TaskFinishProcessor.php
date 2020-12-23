<?php

namespace CorporateTrainingBundle\Biz\UserDailyLearnRecord\EventProcessor;

class TaskFinishProcessor extends AbstractEventProcessor
{
    public function waveRecord($recordId, $fields)
    {
        $this->getUserDailyLearnRecordService()->waveFinishedTaskNumById($recordId);

        if (!empty($fields['courseStatus'])) {
            $finishedCourses = $this->getUserDailyLearnRecordService()->getRecordByUserIdAndCourseIdAndCourseStatus($fields['userId'], $fields['courseId'], 1);
            if (!$finishedCourses) {
                return $this->getUserDailyLearnRecordService()->updateDailyRecord($recordId, array('courseStatus' => $fields['courseStatus']));
            }
        }
    }
}
