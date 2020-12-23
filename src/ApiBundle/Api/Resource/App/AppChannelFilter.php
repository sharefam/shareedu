<?php

namespace ApiBundle\Api\Resource\App;

use ApiBundle\Api\Resource\Classroom\ClassroomFilter;
use ApiBundle\Api\Resource\CourseSet\CourseSetFilter;
use ApiBundle\Api\Resource\Filter;
use CorporateTrainingBundle\Api\Resource\OfflineActivity\OfflineActivityFilter;
use CorporateTrainingBundle\Api\Resource\ProjectPlan\ProjectPlanFilter;

class AppChannelFilter extends Filter
{
    protected $publicFields = array(
        'title', 'type', 'data', 'showCount', 'actualCount', 'orderType', 'categoryId', 'defaultCourseId'
    );

    protected function publicFields(&$data)
    {
        if (in_array($data['type'], array('course', 'live', 'publicCourse', 'departmentCourse'))) {
            $courseSetFilter = new CourseSetFilter();
            $courseSetFilter->setMode(Filter::SIMPLE_MODE);
            $courseSetFilter->filters($data['data']);
        }

        if ($data['type'] == 'classroom') {
            $classroomFilter = new ClassroomFilter();
            $classroomFilter->setMode(Filter::SIMPLE_MODE);
            $classroomFilter->filters($data['data']);
        }

        if ($data['type'] == 'offlineActivity') {
            $offlineActivityFilter = new OfflineActivityFilter();
            $offlineActivityFilter->setMode(Filter::SIMPLE_MODE);
            $offlineActivityFilter->filters($data['data']);
        }

        if ($data['type'] == 'projectPlan') {
            $projectPlanFilter = new ProjectPlanFilter();
            $projectPlanFilter->setMode(Filter::SIMPLE_MODE);
            $projectPlanFilter->filters($data['data']);
        }
    }
}
