<?php

namespace CorporateTrainingBundle\Controller\Classroom;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\Classroom\CourseController as BaseController;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseService;
use CorporateTrainingBundle\Biz\ResourceUsePermissionShared\Service\ResourceUsePermissionSharedService;
use Symfony\Component\HttpFoundation\Request;

class CourseController extends BaseController
{
    public function pickAction($classroomId)
    {
        return $this->render(
            'classroom-manage/course/course-pick-modal.html.twig',
            array(
                'classroomId' => $classroomId,
            )
        );
    }

    public function ajaxPickAction(Request $request, $classroomId)
    {
        $key = $request->request->get('key', '');

        $conditions = array(
            'status' => 'published',
            'title' => "%{$key}%",
            'categoryId' => $request->request->get('categoryId', ''),
        );

        $activeCourses = $this->getClassroomService()->findActiveCoursesByClassroomId($classroomId);
        if (!empty($activeCourses)) {
            $conditions['excludeIds'] = ArrayToolkit::column($activeCourses, 'courseSetId');
        }
        $user = $this->getCurrentUser();

        $conditions['orgIds'] = $this->prepareOrgIds($conditions);

        if (!$user->isSuperAdmin()) {
            $courseSets = $this->getCourseSetService()->findTeachingCourseSetsByUserId($user['id']);
            $conditions['defaultCourseIds'] = ArrayToolkit::column($courseSets, 'defaultCourseId');
            unset($conditions['orgIds']);
        }

        $paginator = new Paginator(
            $request,
            $this->getCourseSetService()->countCourseSets($conditions),
            5
        );
        $paginator->setBaseUrl($this->generateUrl('classroom_courses_pick_ajax', array('classroomId' => $classroomId)));

        $courseSets = $this->getCourseSetService()->searchCourseSets(
            $conditions,
            array('updatedTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUsers($courseSets);

        return $this->render(
            'classroom-manage/course-select-list.html.twig',
            array(
                'users' => $users,
                'courseSets' => $courseSets,
                'classroomId' => $classroomId,
                'type' => 'ajax_pagination',
                'paginator' => $paginator,
                'dataType' => 'manage',
            )
        );
    }

    public function usePermissionAjaxPickAction(Request $request, $classroomId)
    {
        $key = $request->request->get('key', '');
        $conditions = array(
            'status' => 'published',
            'title' => "%{$key}%",
            'categoryId' => $request->request->get('categoryId', ''),
        );

        $recordConditions = array(
            'toUserId' => $this->getCurrentUser()->getId(),
            'resourceType' => 'courseSet',
        );
        $activeCourses = $this->getClassroomService()->findActiveCoursesByClassroomId($classroomId);

        if (!empty($activeCourses)) {
            $recordConditions['excludeResourceIds'] = ArrayToolkit::column($activeCourses, 'courseSetId');
        }

        $records = $this->getResourceUsePermissionSharedService()->searchSharedRecords($recordConditions, array(), 0, PHP_INT_MAX, array('resourceId'));
        $conditions['ids'] = empty($records) ? array(-1) : ArrayToolkit::column($records, 'resourceId');

        $paginator = new Paginator(
            $request,
            $this->getCourseSetService()->countCourseSets($conditions),
            5
        );

        $paginator->setBaseUrl($this->generateUrl('classroom_use_permission_courses_pick_ajax', array('classroomId' => $classroomId)));

        $courseSets = $this->getCourseSetService()->searchCourseSets(
            $conditions,
            array('updatedTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUsers($courseSets);

        return $this->render(
            'classroom-manage/course-select-list.html.twig',
            array(
                'users' => $users,
                'courseSets' => $courseSets,
                'classroomId' => $classroomId,
                'type' => 'ajax_pagination',
                'paginator' => $paginator,
                'dataType' => 'usePermission',
            )
        );
    }

    public function searchAction(Request $request, $classroomId)
    {
        $key = $request->request->get('key');

        $activeCourses = $this->getClassroomService()->findActiveCoursesByClassroomId($classroomId);
        $excludeIds = ArrayToolkit::column($activeCourses, 'courseSetId');

        $conditions = array('title' => "%{$key}%");
        $conditions['status'] = 'published';
        $conditions['excludeIds'] = $excludeIds;

        $user = $this->getCurrentUser();
        if (!$user->isAdmin() && !$user->isSuperAdmin()) {
            $conditions['creator'] = $user['id'];
        }

        $conditions['orgIds'] = $this->prepareOrgIds($conditions);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseSetService()->countCourseSets($conditions),
            5
        );

        $courseSets = $this->getCourseSetService()->searchCourseSets(
            $conditions,
            array('updatedTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUsers($courseSets);

        return $this->render(
            'course/course-select-list.html.twig',
            array(
                'users' => $users,
                'courseSets' => $courseSets,
                'paginator' => $paginator,
                'classroomId' => $classroomId,
                'type' => 'ajax_pagination',
            )
        );
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return ResourceUsePermissionSharedService
     */
    protected function getResourceUsePermissionSharedService()
    {
        return $this->createService('ResourceUsePermissionShared:ResourceUsePermissionSharedService');
    }
}
