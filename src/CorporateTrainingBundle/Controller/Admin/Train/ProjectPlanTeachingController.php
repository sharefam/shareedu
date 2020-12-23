<?php

namespace CorporateTrainingBundle\Controller\Admin\Train;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;

class ProjectPlanTeachingController extends BaseController
{
    public function teachingAction(Request $request, $filter = 'course')
    {
        if ('course' != $filter) {
            return $this->forward('CorporateTrainingBundle:Admin/Train/ProjectPlanTeaching:offlineCourses', array('request' => $request));
        }

        return $this->forward('CorporateTrainingBundle:Admin/Train/ProjectPlanTeaching:courses', array('request' => $request));
    }

    public function coursesAction(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user->isTeacher()) {
            return $this->createMessageResponse('error', 'my.classroom.teaching.no_permission');
        }

        $courses = $this->getCourseService()->findTeachingCoursesByUserId($user['id']);
        $courseIds = empty($courses) ? array(-1) : ArrayToolkit::column($courses, 'id');

        $type = 'course';
        $itemsCount = $this->getProjectPlanService()->countProjectPlanItems(array('targetIds' => $courseIds, 'targetType' => $type));

        $paginator = new Paginator(
            $request,
            $itemsCount,
            20
        );

        $items = $this->getProjectPlanService()->searchProjectPlanItems(
            array('targetIds' => $courseIds, 'targetType' => $type),
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $teachingCourses = array();
        foreach ($items as $key => $item) {
            $course = $this->getCourseService()->getCourse($item['targetId']);
            $projectPlan = $this->getProjectPlanService()->getProjectPlan($item['projectPlanId']);
            $projectPlanMembers = $this->getProjectPlanMemberService()->findMembersByProjectPlanId($projectPlan['id']);
            $userIds = empty($projectPlanMembers) ? array(-1) : ArrayToolkit::column($projectPlanMembers, 'userId');

            $courseData = $this->createProjectPlanStrategy($type)->buildCoursesData($item, $userIds);

            $teachingCourses[$key] = array_merge($course, array('item' => $item), array('projectPlan' => $projectPlan), array('projectPlanMembers' => $projectPlanMembers), array('courseData' => $courseData));
        }

        return $this->render(
            'CorporateTrainingBundle::admin/train/teach-manage/project-plan-teaching/courses.html.twig',
            array(
                'filter' => 'course',
                'teachingCourses' => $teachingCourses,
                'paginator' => $paginator,
            )
        );
    }

    public function offlineCoursesAction(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user->isTeacher()) {
            return $this->createMessageResponse('error', 'my.classroom.teaching.no_permission');
        }

        $teachingOfflineCourses = $this->getOfflineCourseService()->searchOfflineCourses(array('teacherId' => $user['id'], 'excludeStatuses' => array('closed')), array('createdTime' => 'ASC'), 0, PHP_INT_MAX);
        $offlineCourseIds = empty($teachingOfflineCourses) ? array(-1) : ArrayToolkit::column($teachingOfflineCourses, 'id');

        $paginator = new Paginator(
            $request,
            count($teachingOfflineCourses),
            20
        );
        $teachingOfflineCourses = $this->getOfflineCourseService()->searchOfflineCourses(
            array('ids' => $offlineCourseIds),
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        foreach ($teachingOfflineCourses as &$course) {
            $course['projectPlan'] = $this->getProjectPlanService()->getProjectPlan($course['projectPlanId']);
            $course = $this->getOfflineCourseService()->buildOfflineCourseStatistic($course);
        }

        return $this->render(
            'CorporateTrainingBundle::admin/train/teach-manage/project-plan-teaching/offline-courses.html.twig',
            array(
                'filter' => 'offlineCourse',
                'teachingCourses' => $teachingOfflineCourses,
                'paginator' => $paginator,
            )
        );
    }

    protected function createProjectPlanStrategy($type)
    {
        return $this->getBiz()->offsetGet('projectPlan_item_strategy_context')->createStrategy($type);
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\Impl\ProjectPlanServiceImpl
     */
    protected function getProjectPlanService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:ProjectPlanService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\Impl\MemberServiceImpl
     */
    protected function getProjectPlanMemberService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:MemberService');
    }

    /**
     * @return \Biz\Course\Service\Impl\CourseServiceImpl
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return \Biz\Activity\Service\Impl\ActivityServiceImpl
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineCourse\Service\Impl\TaskServiceImpl
     */
    protected function getOfflineCourseTaskService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineCourse:TaskService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineCourse\Service\Impl\MemberServiceImpl
     */
    protected function getOfflineCourseMemberService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineCourse:MemberService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineCourse\Service\Impl\OfflineCourseServiceImpl
     */
    protected function getOfflineCourseService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineCourse:OfflineCourseService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineCourse\Service\Impl\TaskServiceImpl
     */
    protected function getOfflineCourseTaskResultService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineCourse:TaskService');
    }
}
