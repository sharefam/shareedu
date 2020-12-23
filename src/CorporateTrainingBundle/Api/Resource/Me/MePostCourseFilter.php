<?php

namespace CorporateTrainingBundle\Api\Resource\Me;

use ApiBundle\Api\Resource\CourseSet\CourseSetFilter;
use ApiBundle\Api\Resource\Filter;

class MePostCourseFilter extends Filter
{
    protected $publicFields = array(
        'courseId', 'courseSet', 'compulsoryTaskNum', 'learnedCompulsoryTaskNum', 'totalLearnTime',
    );

    protected function publicFields(&$data)
    {
        $courseSetFilter = new CourseSetFilter();
        $courseSetFilter->setMode(Filter::SIMPLE_MODE);
        $courseSetFilter->filter($data['courseSet']);
    }
}
