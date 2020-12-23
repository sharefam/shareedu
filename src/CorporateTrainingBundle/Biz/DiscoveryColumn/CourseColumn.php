<?php

namespace CorporateTrainingBundle\Biz\DiscoveryColumn;

use CorporateTrainingBundle\Biz\Course\Service\CourseSetService;

class CourseColumn extends BaseColumn
{
    public function buildColumn($column)
    {
        $column['resourceType'] = 'courseSet';
        $courseSets = $this->getCourseSetService()->searchCourseSets(
            $this->getDiscoveryColumnService()->determineConditions($column),
            $this->getDiscoveryColumnService()->determineSort($column),
            0,
            $column['showCount']
        );
        $column['data'] = $courseSets;
        $column['actualCount'] = count($courseSets);

        return $column;
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }
}
