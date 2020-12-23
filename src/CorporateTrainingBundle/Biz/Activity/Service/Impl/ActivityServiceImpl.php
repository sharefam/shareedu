<?php

namespace CorporateTrainingBundle\Biz\Activity\Service\Impl;

use Biz\Activity\Service\Impl\ActivityServiceImpl as BaseActivityService;

class ActivityServiceImpl extends BaseActivityService
{
    public function getActivityByMediaIdAndMediaType($mediaId, $mediaType)
    {
        return $this->getActivityDao()->getByMediaIdAndMediaType($mediaId, $mediaType);
    }

    public function findActivitiesByCourseIdsAndType($courseIds, $type, $fetchMedia = false)
    {
        $conditions = array(
            'courseIds' => $courseIds,
            'mediaType' => $type,
        );
        $activities = $this->getActivityDao()->search($conditions, null, 0, PHP_INT_MAX);

        return $this->prepareActivities($fetchMedia, $activities);
    }
}
