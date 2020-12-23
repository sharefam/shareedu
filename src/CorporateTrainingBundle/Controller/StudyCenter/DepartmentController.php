<?php

namespace CorporateTrainingBundle\Controller\StudyCenter;

use AppBundle\Common\Paginator;
use Symfony\Component\HttpFoundation\Request;
use CorporateTrainingBundle\Controller\StudyCenter\StudyCenterBaseController as BaseController;

class DepartmentController extends BaseController
{
    public function coursesAction(Request $request, $category)
    {
        $user = $this->getCurrentUser();
        $conditions = $request->query->all();

        $courseSetting = $this->getSettingService()->get('course', array());

        list($conditions, $tags) = $this->mergeConditionsByTag($conditions, 'course-set');

        list($conditions, $categoryArray, $category, $categoryArrayDescription, $categoryParent) = $this->mergeConditionsByCategory(
            $conditions,
            $category
        );

        if (!isset($courseSetting['explore_default_orderBy'])) {
            $courseSetting['explore_default_orderBy'] = 'latest';
        }

        $orderBy = 'recommendedSeq' == $courseSetting['explore_default_orderBy'] ? 'studentNum' : $courseSetting['explore_default_orderBy'];
        $orderBy = empty($conditions['orderBy']) ? $orderBy : $conditions['orderBy'];
        unset($conditions['orderBy']);

        $conditions['status'] = 'published';
        $conditions['ids'] = $this->getResourceVisibleScopeService()->findDepartmentVisibleResourceIdsByResourceTypeAndUserId('courseSet', $this->getCurrentUser()->getId());

        $isFilterSpread = isset($conditions['isFilterSpread']) ? $conditions['isFilterSpread'] : 'false';

        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseSetService()->countCourseSets($conditions),
            9
        );

        $courseSets = $this->getCourseSetService()->searchCourseSets(
            $conditions,
            $orderBy,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render(
            'study-center/department-courses/courses.html.twig',
            array(
                'user' => $user,
                'category' => $category,
                'courseSets' => $courseSets,
                'paginator' => $paginator,
                'categoryArray' => $categoryArray,
                'categoryArrayDescription' => $categoryArrayDescription,
                'categoryParent' => $categoryParent,
                'group' => 'course',
                'isFilterSpread' => $isFilterSpread,
                'tags' => $tags,
                'orderBy' => $orderBy ?: null,
            )
        );
    }

    public function classroomsAction(Request $request, $category)
    {
        $user = $this->getCurrentUser();

        $conditions = $request->query->all();

        $conditions['status'] = 'published';
        $conditions['showable'] = 1;

        $classroomSetting = $this->getSettingService()->get('classroom');

        list($conditions, $tags) = $this->mergeConditionsByTag($conditions, 'classroom');

        list($conditions, $categoryArray, $category, $categoryArrayDescription, $categoryParent) = $this->mergeConditionsByCategory(
            $conditions,
            $category
        );
        $conditions['classroomIds'] = $this->getResourceVisibleScopeService()->findDepartmentVisibleResourceIdsByResourceTypeAndUserId('classroom', $this->getCurrentUser()->getId());

        if (isset($conditions['ids'])) {
            $conditions['classroomIds'] = array_intersect($conditions['ids'], $conditions['classroomIds']);
            unset($conditions['ids']);
        }

        if (!isset($classroomSetting['explore_default_orderBy'])) {
            $classroomSetting['explore_default_orderBy'] = 'createdTime';
        }

        $orderBy = empty($conditions['orderBy']) ? $classroomSetting['explore_default_orderBy'] : $conditions['orderBy'];

        if ('recommendedSeq' == $orderBy) {
            $conditions['recommended'] = 1;
            $orderBys = array($orderBy => 'asc');
        } else {
            $orderBys = array($orderBy => 'desc');
        }

        unset($conditions['orderBy']);

        $isFilterSpread = isset($conditions['isFilterSpread']) ? $conditions['isFilterSpread'] : 'false';

        $paginator = new Paginator(
            $this->get('request'),
            $this->getClassroomService()->countClassrooms($conditions),
            9
        );

        $classrooms = $this->getClassroomService()->searchClassrooms(
            $conditions,
            $orderBys,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render(
            'study-center/department-courses/classrooms.html.twig',
            array(
                'user' => $user,
                'category' => $category,
                'classrooms' => $classrooms,
                'paginator' => $paginator,
                'categoryArray' => $categoryArray,
                'categoryArrayDescription' => $categoryArrayDescription,
                'categoryParent' => $categoryParent,
                'group' => 'classroom',
                'isFilterSpread' => $isFilterSpread,
                'tags' => $tags,
                'orderBy' => $orderBy ?: null,
            )
        );
    }
}
