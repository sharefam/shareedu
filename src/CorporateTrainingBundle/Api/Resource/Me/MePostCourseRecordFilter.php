<?php

namespace CorporateTrainingBundle\Api\Resource\Me;

use ApiBundle\Api\Resource\Filter;

class MePostCourseRecordFilter extends Filter
{
    protected $publicFields = array(
        'courseName', 'teacherName', 'totalLearnTime', 'progress',
    );
}
