<?php

namespace CorporateTrainingBundle\Api\Resource\Me;

use ApiBundle\Api\Resource\Filter;

class MeProjectPlanRecordFilter extends Filter
{
    protected $publicFields = array(
        'projectPlanId', 'projectPlanName', 'startTime', 'endTime', 'progress',
    );
}
