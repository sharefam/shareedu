<?php

namespace CorporateTrainingBundle\Controller\ProjectPlan\Item;

use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class OfflineCourseItemController extends BaseItemController
{
    public function createAction(Request $request, $projectPlanId)
    {
        $this->hasManageRole();

        return $this->render(
            'project-plan/item/offline-course.html.twig',
            array(
                'projectPlanId' => $projectPlanId,
            )
        );
    }

    public function updateAction(Request $request, $projectPlanId, $offlineCourseId)
    {
        $this->hasManageRole();

        $offlineCourse = $this->getOfflineCourseService()->getOfflineCourse($offlineCourseId);
        $teachers = $this->getTeacherId($offlineCourseId);
        $item = $this->getProjectPlanService()->getProjectPlanItemByProjectPlanIdAndTargetIdAndTargetType($projectPlanId, $offlineCourseId, 'offline_course');
        $tasks = $this->getTaskService()->findTasksByOfflineCourseId($offlineCourseId);

        return $this->render(
            'project-plan/item/offline-course.html.twig',
            array(
                'offlineCourse' => $offlineCourse,
                'teachers' => $teachers,
                'projectPlanId' => $projectPlanId,
                'item' => $item,
                'taskNum' => count($tasks),
            )
        );
    }

    public function listAction(Request $request, $id)
    {
        $projectPlan = $this->getProjectPlanService()->getProjectPlan($id);

        $conditions = array(
            'projectPlanId' => $id,
            'targetType' => 'offline_course',
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

        foreach ($items as &$item) {
            $item = $this->buildOfflineCourseStatistic($item);
        }

        $offlineCourses = $this->getOfflineCourseService()->findOfflineCoursesByIds(ArrayToolkit::column($items, 'targetId'));

        return $this->render(
            'project-plan/item-list/offline-course-list.html.twig',
            array(
                'items' => $items,
                'paginator' => $paginator,
                'projectPlan' => $projectPlan,
                'courses' => ArrayToolkit::index($offlineCourses, 'id'),
            )
        );
    }

    protected function buildOfflineCourseStatistic($item)
    {
        $conditions = array('type' => 'offlineCourse', 'offlineCourseId' => $item['targetId']);

        $offlineCourseTasks = $this->getOfflineCourseTaskService()->searchTasks(
            $conditions,
            array(),
            0,
            PHP_INT_MAX
        );
        $taskIds = ArrayToolkit::column($offlineCourseTasks, 'id');
        $taskIds = empty($taskIds) ? array(-1) : $taskIds;
        $members = $this->getProjectPlanMemberService()->searchProjectPlanMembers(
            array('projectPlanId' => $item['projectPlanId']),
            array(),
            0,
            PHP_INT_MAX
        );
        $course['memberCount'] = count($members);
        $userIds = empty($members) ? array(-1) : ArrayToolkit::column($members, 'userId');
        $item['memberCount'] = $this->getProjectPlanMemberService()->countProjectPlanMembers(array('projectPlanId' => $item['projectPlanId']));

        $hasHomeTasks = $this->getOfflineCourseTaskService()->searchTasks(array_merge($conditions, array('hasHomework' => 1)), array(), 0, PHP_INT_MAX);
        $item['hasHomeTaskCount'] = count($hasHomeTasks) * $item['memberCount'];
        $taskResultConditions = array('offlineCourseId' => $item['targetId'], 'taskIds' => $taskIds, 'userIds' => $userIds);
        $passHomeworkResult = $this->getOfflineCourseTaskService()->searchTaskResults(array_merge($taskResultConditions, array('homeworkStatus' => 'passed')), array(), 0, PHP_INT_MAX);
        $item['homeWorkSubmitCount'] = $this->getOfflineCourseTaskResultService()->countTaskResults(array_merge($taskResultConditions, array('homeworkStatus' => 'submitted')));
        $item['passHomeworkCount'] = count($passHomeworkResult);

        $item['attendTaskCount'] = count($offlineCourseTasks) * $item['memberCount'];
        $attendResult = $this->getOfflineCourseTaskService()->searchTaskResults(array_merge($taskResultConditions, array('attendStatus' => 'attended')), array(), 0, PHP_INT_MAX);
        $item['attendCount'] = count($attendResult);

        return $item;
    }

    protected function getTeacherId($offlineCourseId)
    {
        $teachers = $this->getOfflineCourseService()->findTeachersByOfflineCourseId($offlineCourseId);

        if (!empty($teachers)) {
            foreach ($teachers as &$teacher) {
                $teacher['avatar'] = $this->get('web.twig.extension')->avatarPath($teacher, 'small');
            }
        }

        return $teachers;
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
    protected function getTaskService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineCourse:TaskService');
    }

    /**
     * @return \Biz\Course\Service\Impl\CourseServiceImpl
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineCourse\Service\Impl\TaskServiceImpl
     */
    protected function getOfflineCourseTaskResultService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineCourse:TaskService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\Impl\MemberServiceImpl
     */
    protected function getProjectPlanMemberService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:MemberService');
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

    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }
}
