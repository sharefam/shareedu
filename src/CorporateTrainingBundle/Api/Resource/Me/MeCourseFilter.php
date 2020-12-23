<?php

namespace CorporateTrainingBundle\Api\Resource\Me;

use ApiBundle\Api\Resource\CourseSet\CourseSetFilter;
use ApiBundle\Api\Resource\Filter;

class MeCourseFilter extends Filter
{
    protected $publicFields = array(
        'id', 'title', 'learnedNum', 'courseSet', 'compulsoryTaskNum', 'learnedCompulsoryTaskNum', 'totalLearnTime',
    );

    protected function publicFields(&$data)
    {
        $courseSetFilter = new CourseSetFilter();
        $courseSetFilter->setMode(Filter::SIMPLE_MODE);
        $courseSetFilter->filter($data['courseSet']);
    }
}
