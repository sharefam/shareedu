<?php

namespace CorporateTrainingBundle\Biz\DiscoveryColumn;

use CorporateTrainingBundle\Biz\Classroom\Service\ClassroomService;

class ClassroomColumn extends BaseColumn
{
    public function buildColumn($column)
    {
        $column['resourceType'] = 'classroom';
        $conditions = $this->getDiscoveryColumnService()->determineConditions($column);

        if (isset($conditions['ids'])) {
            $conditions['classroomIds'] = $conditions['ids'];
            unset($conditions['ids']);
        }

        $classrooms = $this->getClassroomService()->searchClassrooms(
            $conditions,
            $this->getDiscoveryColumnService()->determineSort($column),
            0,
            $column['showCount']
        );

        $column['data'] = $classrooms;
        $column['actualCount'] = count($classrooms);

        return $column;
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }
}
