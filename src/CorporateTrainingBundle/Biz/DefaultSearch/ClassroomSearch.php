<?php

namespace CorporateTrainingBundle\Biz\DefaultSearch;

use AppBundle\Common\Paginator;

class ClassroomSearch extends AbstractSearch
{
    public function search($request, $keywords)
    {
        $conditions = $this->prepareSearchConditions($keywords);

        $classroomNum = $this->getClassroomService()->countClassrooms($conditions);

        $paginator = new Paginator(
            $request,
            $classroomNum,
            10
        );

        $classrooms = $this->getClassroomService()->searchClassrooms(
            $conditions,
            array('updatedTime' => 'desc'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return array($classrooms, $paginator);
    }

    public function count($request, $keywords)
    {
        $conditions = $this->prepareSearchConditions($keywords);
        $classroomNum = $this->getClassroomService()->countClassrooms($conditions);

        return $classroomNum;
    }

    protected function prepareSearchConditions($keywords)
    {
        $user = $this->getCurrentUser();
        $scopeClassroomIds = $this->getResourceVisibleScopeService()->findVisibleResourceIdsByResourceTypeAndUserId('classroom',
            $user['id']);
        $conditions = array(
            'classroomIds' => $scopeClassroomIds,
            'price' => '0.00',
            'status' => 'published',
            'titleLike' => $keywords,
        );

        return $conditions;
    }

    /**
     * @return \Biz\Classroom\Service\ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }
}
