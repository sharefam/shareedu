<?php

namespace CorporateTrainingBundle\Biz\DiscoveryColumn;

use CorporateTrainingBundle\Biz\Course\Service\CourseSetService;
use CorporateTrainingBundle\Biz\User\Service\UserService;

class DepartmentCourseColumn extends BaseColumn
{
    public function buildColumn($column)
    {
        $conditions = array(
            'status' => 'published',
        );
        $conditions['ids'] = $this->getResourceVisibleScopeService()->findDepartmentVisibleResourceIdsByResourceTypeAndUserId('courseSet', $this->getCurrentUser()->getId());

        $courseSets = $this->getCourseSetService()->searchCourseSets(
            $conditions,
            array('createdTime' => 'DESC'),
            0,
            $column['showCount']
        );

        $column['data'] = $courseSets;
        $column['actualCount'] = count($courseSets);

        return $column;
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }
}
