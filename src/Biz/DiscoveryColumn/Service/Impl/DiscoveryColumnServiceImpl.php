<?php

namespace Biz\DiscoveryColumn\Service\Impl;

use Biz\BaseService;
use AppBundle\Common\ArrayToolkit;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseSetService;
use Biz\DiscoveryColumn\Service\DiscoveryColumnService;
use CorporateTrainingBundle\Biz\ResourceScope\Service\ResourceVisibleScopeService;

class DiscoveryColumnServiceImpl extends BaseService implements DiscoveryColumnService
{
    public function getDiscoveryColumn($id)
    {
        return $this->getDiscoveryColumnDao()->get($id);
    }

    public function updateDiscoveryColumn($id, $fields)
    {
        $fields = ArrayToolkit::parts($fields, array('categoryId', 'orderType', 'type', 'showCount', 'title', 'seq', 'isDisplay'));

        return $this->getDiscoveryColumnDao()->update($id, $fields);
    }

    public function deleteDiscoveryColumn($id)
    {
        return $this->getDiscoveryColumnDao()->delete($id);
    }

    public function addDiscoveryColumn($fields)
    {
        return $this->getDiscoveryColumnDao()->create($fields);
    }

    public function findDiscoveryColumnByTitle($title)
    {
        return $this->getDiscoveryColumnDao()->findByTitle($title);
    }

    public function getAllDiscoveryColumns()
    {
        return $this->getDiscoveryColumnDao()->findAllOrderBySeq();
    }

    public function getDisplayData()
    {
        $columns = $this->getDiscoveryColumnDao()->findAllOrderBySeq();

        foreach ($columns as &$column) {
            if ('course' == $column['type'] || 'live' == $column['type']) {
                $column['resourceType'] = 'courseSet';
                $courseSets = $this->getCourseSetService()->searchCourseSets(
                    $this->determineConditions($column),
                    $this->determineSort($column),
                    0,
                    $column['showCount']
                );

                $column['data'] = $courseSets;
                $column['actualCount'] = count($courseSets);
            }

            if ('classroom' == $column['type']) {
                $column['resourceType'] = 'classroom';
                $classrooms = $this->getClassroomService()->searchClassrooms(
                    $this->determineConditions($column),
                    $this->determineSort($column),
                    0,
                    $column['showCount']
                );

                $column['data'] = $classrooms;
                $column['actualCount'] = count($classrooms);
            }
        }

        return $columns;
    }

    public function determineConditions($column)
    {
        $conditions = array(
            'status' => 'published',
            'showable' => 1,
        );

        if (!empty($column['categoryId'])) {
            $childrenIds = $this->getCategoryService()->findCategoryChildrenIds($column['categoryId']);
            $conditions['categoryIds'] = array_merge(array($column['categoryId']), $childrenIds);
        }

        if ('live' == $column['type']) {
            $conditions['type'] = 'live';
        }

        if ('course' == $column['type']) {
            $conditions['type'] = 'normal';
        }

        if ('recommend' == $column['orderType']) {
            $conditions['recommended'] = 1;
        }

        $conditions['ids'] = $this->getResourceVisibleScopeService()->findVisibleResourceIdsByResourceTypeAndUserId($column['resourceType'], $this->getCurrentUser()->getId());

        return $conditions;
    }

    public function determineSort($column)
    {
        $sortMap = array(
            'hot' => array('studentNum' => 'DESC'),
            'recommend' => array(
                'recommendedSeq' => 'ASC',
                'recommendedTime' => 'DESC',
            ),
        );

        if ($column['orderType'] && !empty($sortMap[$column['orderType']])) {
            return $sortMap[$column['orderType']];
        } else {
            return array(
                'createdTime' => 'DESC',
            );
        }
    }

    public function sortDiscoveryColumns(array $ids)
    {
        $index = 1;
        foreach ($ids as $key => $id) {
            $this->updateDiscoveryColumn($id, array('seq' => $index));
            ++$index;
        }
    }

    protected function getDiscoveryColumnDao()
    {
        return $this->createDao('DiscoveryColumn:DiscoveryColumnDao');
    }

    /**
     * @return CourseSetService
     */
    private function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return ResourceVisibleScopeService
     */
    protected function getResourceVisibleScopeService()
    {
        return $this->createService('ResourceScope:ResourceVisibleScopeService');
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    protected function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }

    private function getUserService()
    {
        return $this->createService('User:UserService');
    }

    protected function getOrgService()
    {
        return $this->createService('Org:OrgService');
    }
}
