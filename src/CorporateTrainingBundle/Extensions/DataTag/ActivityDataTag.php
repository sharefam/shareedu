<?php

namespace CorporateTrainingBundle\Extensions\DataTag;

use AppBundle\Extensions\DataTag\CourseBaseDataTag;
use AppBundle\Extensions\DataTag\DataTag;

class ActivityDataTag extends CourseBaseDataTag implements DataTag
{
    public function getData(array $arguments)
    {
        $activity = $this->getActivityService()->getActivity($arguments['activityId'], $arguments['fetchMedia']);

        return $activity;
    }
}
