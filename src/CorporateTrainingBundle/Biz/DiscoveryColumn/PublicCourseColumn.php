<?php

namespace CorporateTrainingBundle\Biz\DiscoveryColumn;

use CorporateTrainingBundle\Biz\Course\Service\CourseSetService;

class PublicCourseColumn extends BaseColumn
{
    public function buildColumn($column)
    {
        $condition = array(
            'status' => 'published',
        );
        $condition['ids'] = $this->getResourceVisibleScopeService()->findPublicVisibleResourceIdsByResourceTypeAndUserId('courseSet', $this->getCurrentUser()->getId());

        $courseSets = $this->getCourseSetService()->searchCourseSets(
            $condition,
            array('createdTime' => 'DESC'),
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
