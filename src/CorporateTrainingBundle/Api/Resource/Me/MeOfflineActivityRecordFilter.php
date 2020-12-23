<?php

namespace CorporateTrainingBundle\Api\Resource\Me;

use ApiBundle\Api\Resource\Filter;

class MeOfflineActivityRecordFilter extends Filter
{
    protected $publicFields = array(
        'offlineActivityName', 'offlineActivityPlace', 'startTime', 'endTime', 'categoryName', 'passedStatus', 'attendedStatus',
    );
}
