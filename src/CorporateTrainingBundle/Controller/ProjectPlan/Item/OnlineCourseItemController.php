<?php

namespace CorporateTrainingBundle\Controller\ProjectPlan\Item;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use CorporateTrainingBundle\Biz\ResourceUsePermissionShared\Service\ResourceUsePermissionSharedService;
use Symfony\Component\HttpFoundation\Request;

class OnlineCourseItemController extends BaseItemController
{
    public function createAction(Request $request, $projectPlanId)
    {
        $this->hasManageRole();

        return $this->render(
            'project-plan/item/online-course.html.twig',
            array(
                'projectPlanId' => $projectPlanId,
            )
        );
    }

    public function updateAction(Request $request, $id)
    {
        $this->hasManageRole();

        $item = $this->getProjectPlanService()->getProjectPlanItem($id);
        $course = $this->getCourseService()->getCourse($item['targetId']);

        if ($item['targetId'] != $course['id']) {
            throw new \InvalidArgumentException('DATA ERROR');
        }

        $projectPlanCourse = array(
            'id' => $course['id'],
            'title' => $course['title'],
            'startTime' => $item['startTime'],
            'endTime' => $item['endTime'],
        );

        return $this->render(
            'project-plan/item/online-course.html.twig',
            array(
                'id' => $id,
                'course' => $course,
                'projectPlanId' => $item['projectPlanId'],
                'projectPlanCourse' => $projectPlanCourse,
            )
        );
    }

    public function listAction(Request $request, $id)
    {
        $projectPlan = $this->getProjectPlanService()->getProjectPlan($id);
        $conditions = array(
            'projectPlanId' => $id,
            'targetType' => 'course',
        );
        $itemNum = $this->getProjectPlanService()->countProjectPlanItems($conditions);

        $paginator = new Paginator(
            $request,
            $itemNum,
            10
        );

        $items = $this->getProjectPlanService()->searchProjectPlanItems(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $projectPlanMembers = $this->getProjectPlanMemberService()->searchProjectPlanMembers(array('projectPlanId' => $id), array(), 0, PHP_INT_MAX);
        $projectPlanMemberUserIds = empty($projectPlanMembers) ? array(-1) : ArrayToolkit::column($projectPlanMembers, 'userId');

        foreach ($items as &$item) {
            $course = $this->getCourseService()->getCourse($item['targetId']);
            $courseData = $this->createProjectPlanStrategy()->buildCoursesData($item, $projectPlanMemberUserIds);

            $item = array_merge($item, array('projectPlanMembers' => $projectPlanMembers), array('course' => $course), array('courseData' => $courseData));
        }

        return $this->render(
            'project-plan/item-list/course-list.html.twig',
            array(
                'items' => $items,
                'paginator' => $paginator,
                'projectPlan' => $projectPlan,
            )
        );
    }

    public function matchCourseAction(Request $request)
    {
        $ids = $request->request->get('ids');
        $selectIds = $request->request->get('selectedIds', array());

        $courses = $this->getCourseSetService()->searchCourseSets(array('ids' => $ids, 'excludeIds' => $selectIds), array(), 0, PHP_INT_MAX, array('id', 'defaultCourseId', 'title'));

        return $this->createJsonResponse($courses);
    }

    public function pickAction(Request $request, $projectPlanId)
    {
        return $this->render(
            'project-plan/online-course/course-pick-modal.html.twig',
            array(
                'projectPlanId' => $projectPlanId,
            )
        );
    }

    public function ajaxMatchManageCourseAction(Request $request, $projectPlanId)
    {
        $key = $request->request->get('key', '');

        $conditions = array(
            'status' => 'published',
            'title' => "%{$key}%",
            'categoryId' => $request->request->get('categoryId', ''),
        );

        $user = $this->getCurrentUser();

        $conditions['orgIds'] = $this->prepareOrgIds($conditions);

        if (!$user->isSuperAdmin()) {
            $courseSets = $this->getCourseSetService()->findTeachingCourseSetsByUserId($user['id']);
            $conditions['defaultCourseIds'] = ArrayToolkit::column($courseSets, 'defaultCourseId');
            unset($conditions['orgIds']);
        }
        $items = $this->getProjectPlanService()->findProjectPlanItemsByProjectPlanIdAndTargetType($projectPlanId, 'course');
        if (!empty($items)) {
            $conditions['excludeDefaultCourseIds'] = ArrayToolkit::column($items, 'targetId');
        }

        $paginator = new Paginator(
            $request,
            $this->getCourseSetService()->countCourseSets($conditions),
            5
        );
        $paginator->setBaseUrl($this->generateUrl('project_plan_ajax_match_manage_course', array('projectPlanId' => $projectPlanId)));

        $courseSets = $this->getCourseSetService()->searchCourseSets(
            $conditions,
            array('updatedTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUsers($courseSets);

        return $this->render(
            'project-plan/online-course/course-select-list.html.twig',
            array(
                'projectPlanId' => $projectPlanId,
                'users' => $users,
                'courseSets' => $courseSets,
                'type' => 'ajax_pagination',
                'paginator' => $paginator,
                'dataType' => 'manage',
            )
        );
    }

    public function ajaxMatchUsePermissionCourseAction(Request $request, $projectPlanId)
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
        $records = $this->getResourceUsePermissionSharedService()->searchSharedRecords($recordConditions, array(), 0, PHP_INT_MAX, array('resourceId'));
        $items = $this->getProjectPlanService()->findProjectPlanItemsByProjectPlanIdAndTargetType($projectPlanId, 'course');
        if (!empty($items)) {
            $conditions['excludeDefaultCourseIds'] = ArrayToolkit::column($items, 'targetId');
        }
        $conditions['ids'] = empty($records) ? array(-1) : ArrayToolkit::column($records, 'resourceId');

        $paginator = new Paginator(
            $request,
            $this->getCourseSetService()->countCourseSets($conditions),
            5
        );

        $paginator->setBaseUrl($this->generateUrl('project_plan_ajax_match_use_permission_course', array('projectPlanId' => $projectPlanId)));

        $courseSets = $this->getCourseSetService()->searchCourseSets(
            $conditions,
            array('updatedTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUsers($courseSets);

        return $this->render(
            'project-plan/online-course/course-select-list.html.twig',
            array(
                'projectPlanId' => $projectPlanId,
                'users' => $users,
                'courseSets' => $courseSets,
                'type' => 'ajax_pagination',
                'paginator' => $paginator,
                'dataType' => 'usePermission',
            )
        );
    }

    protected function getUsers($courseSets)
    {
        $userIds = array();
        foreach ($courseSets as &$courseSet) {
            // $tags = $this->getTagService()->findTagsByOwner(array('ownerType' => 'course', 'ownerId' => $course['id']));
            if (!empty($courseSet['tags'])) {
                $tags = $this->getTagService()->findTagsByIds($courseSet['tags']);

                $courseSet['tags'] = ArrayToolkit::column($tags, 'id');
            }
            $userIds = array_merge($userIds, array($courseSet['creator']));
        }

        $users = $this->getUserService()->findUsersByIds($userIds);
        if (!empty($users)) {
            $users = ArrayToolkit::index($users, 'id');
        }

        return $users;
    }

    protected function createProjectPlanStrategy()
    {
        return $this->getBiz()->offsetGet('projectPlan_item_strategy_context')->createStrategy('course');
    }

    /**
     * @return \Biz\Course\Service\Impl\CourseServiceImpl
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }

    protected function getProjectPlanMemberService()
    {
        return $this->createService('ProjectPlan:MemberService');
    }

    protected function getTaskResultService()
    {
        return $this->createService('CorporateTrainingBundle:Task:TaskResultService');
    }

    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return \Biz\Course\Service\Impl\CourseSetServiceImpl
     */
    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }

    private function getTagService()
    {
        return $this->createService('Taxonomy:TagService');
    }

    /**
     * @return ResourceUsePermissionSharedService
     */
    protected function getResourceUsePermissionSharedService()
    {
        return $this->createService('ResourceUsePermissionShared:ResourceUsePermissionSharedService');
    }
}
