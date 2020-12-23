<?php

namespace CorporateTrainingBundle\Controller\OfflineCourse;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\BaseController;
use Biz\User\Service\TokenService;
use SurveyPlugin\Biz\Survey\Service\SurveyMemberService;
use Symfony\Component\HttpFoundation\Request;

class HomeworkManageController extends BaseController
{
    public function homeworkCheckAction(Request $request, $id)
    {
        $taskResult = $this->getOfflineCourseTaskService()->getTaskResult($id);
        if (!$this->getOfflineCourseService()->tryManageOfflineCourse($taskResult['offlineCourseId'])) {
            return $this->createMessageResponse('error', 'offline_course.message.no_permission');
        }

        if ('POST' == $request->getMethod()) {
            $homeworkStatus = $request->request->get('homeworkStatus');

            if ('passed' == $homeworkStatus) {
                $this->getOfflineCourseTaskService()->passHomework($id);
            } else {
                $this->getOfflineCourseTaskService()->unpassHomework($id);
            }

            return $this->createJsonResponse(true);
        }

        return $this->render('offline-course-manage/homework-manage/check.html.twig', array(
            'homeworkRecord' => $taskResult,
            'taskResult' => $taskResult,
        ));
    }

    public function homeworkTaskListAction(Request $request, $id)
    {
        $projectPlanMember = $this->getProjectPlanMember($id);
        $offlineCourse = $this->getOfflineCourseService()->tryManageOfflineCourse($id);
        $conditions = array('offlineCourseId' => $offlineCourse['id'], 'hasHomework' => 1);

        $paginator = new Paginator(
            $request,
            $this->getOfflineCourseTaskService()->countTasks($conditions),
            10
        );

        $offlineCourseTasks = $this->getOfflineCourseTaskService()->searchTasks(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $tasks = $this->manageOfflineCourseTasks($offlineCourseTasks, ArrayToolkit::column($projectPlanMember, 'userId'));

        return $this->render('offline-course-manage/homework-manage/task-list.html.twig',
            array(
                'courseTasks' => $offlineCourseTasks,
                'courseSet' => $offlineCourse,
                'tasks' => ArrayToolkit::index($tasks, 'id'),
                'offlineCourse' => $offlineCourse,
                'offlineCourseExts' => $offlineCourseTasks,
                'paginator' => $paginator,
            ));
    }

    public function homeworkListAction(Request $request, $id)
    {
        $offlineCourseTask = $this->getOfflineCourseTaskService()->getTask($id);
        $offlineCourse = $this->getOfflineCourseService()->getOfflineCourse($offlineCourseTask['offlineCourseId']);

        $projectPlanMember = $this->getProjectPlanMember($offlineCourse['id']);
        $conditions = $request->request->all();
        $conditions['taskId'] = $offlineCourseTask['id'];
        $conditions['userIds'] = ArrayToolkit::column($projectPlanMember, 'userId');
        $conditions = $this->prepareConditions($conditions);
        $paginator = new Paginator(
            $request,
            $this->getOfflineCourseTaskService()->countTaskResults($conditions),
            20
        );
        $paginator->setBaseUrl($this->generateUrl('project_plan_offline_course_homework_ajax_list', array('id' => $id)));

        $taskResults = $this->getOfflineCourseTaskService()->searchTaskResults(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $orgIds = $this->prepareOrgIds($conditions);

        return $this->render('offline-course-manage/homework-manage/index.html.twig', array(
            'taskResults' => $taskResults,
            'offlineCourseTask' => $offlineCourseTask,
            'offlineCourse' => $offlineCourse,
            'orgIds' => implode(',', $orgIds) ?: array(),
            'paginator' => $paginator,
        ));
    }

    public function homeworkAjaxListAction(Request $request, $id)
    {
        $offlineCourseTask = $this->getOfflineCourseTaskService()->getTask($id);
        $offlineCourse = $this->getOfflineCourseService()->getOfflineCourse($offlineCourseTask['offlineCourseId']);

        $projectPlanMember = $this->getProjectPlanMember($offlineCourse['id']);
        $conditions = $request->request->all();
        $conditions['taskId'] = $offlineCourseTask['id'];
        $conditions['userIds'] = ArrayToolkit::column($projectPlanMember, 'userId');
        $conditions = $this->prepareConditions($conditions);
        $paginator = new Paginator(
            $request,
            $this->getOfflineCourseTaskService()->countTaskResults($conditions),
            20
        );

        $taskResults = $this->getOfflineCourseTaskService()->searchTaskResults(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $orgIds = $this->prepareOrgIds($conditions);

        return $this->render('offline-course-manage/homework-manage/list.html.twig', array(
            'taskResults' => $taskResults,
            'offlineCourseTask' => $offlineCourseTask,
            'offlineCourse' => $offlineCourse,
            'orgIds' => implode(',', $orgIds) ?: array(),
            'paginator' => $paginator,
        ));
    }

    protected function getProjectPlanMember($id)
    {
        $projectPlanItem = $this->getProjectPlanService()->getProjectPlanItemByTargetIdAndTargetType($id, 'offline_course');
        $projectPlanMembers = $this->getProjectPlanMemberService()->findMembersByprojectPlanId($projectPlanItem['projectPlanId']);

        return $projectPlanMembers;
    }

    protected function prepareConditions($conditions)
    {
        if (isset($conditions['homeworkStatus']) && 'all' == $conditions['homeworkStatus']) {
            unset($conditions['homeworkStatus']);
        }

        if (isset($conditions['orgIds'])) {
            $orgUserIds = $this->getUserOrgService()->searchUserOrgs(
                array('orgIds' => explode(',', $conditions['orgIds'])),
                array(),
                0,
                PHP_INT_MAX
            );
            $conditions['userIds'] = ArrayToolkit::column($orgUserIds, 'userId');
        }

        if (!empty($conditions['username'])) {
            $userIds = $this->getUserService()->findUserIdsByNickNameOrTrueName($conditions['username']);
            $conditions['userIds'] = (empty($userIds) || empty($conditions['userIds'])) ? array() : array_intersect($conditions['userIds'], $userIds);
        }

        $conditions['userIds'] = empty($conditions['userIds']) ? array(-1) : $conditions['userIds'];

        return $conditions;
    }

    protected function prepareOrgIds($conditions)
    {
        if (!isset($conditions['orgIds'])) {
            $orgIds = $this->getOrgService()->findOrgsByPrefixOrgCodes(array('1.'), array('id'));

            return  ArrayToolkit::column($orgIds, 'id');
        }

        return explode(',', $conditions['orgIds']);
    }

    protected function manageOfflineCourseTasks($tasks, $userIds)
    {
        foreach ($tasks as &$task) {
            $homeworkRecords = $this->getOfflineCourseTaskService()->findHomeworkStatusNumGroupByStatus($task['id'], $userIds);
            $homeworkRecords = ArrayToolkit::index($homeworkRecords, 'homeworkStatus');
            $task['homeworkStatus'] = $homeworkRecords;
        }

        return $tasks;
    }

    /**
     * @return TokenService
     */
    protected function getTokenService()
    {
        return $this->createService('User:TokenService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\User\Service\Impl\UserOrgServiceImpl
     */
    protected function getUserOrgService()
    {
        return $this->createService('CorporateTrainingBundle:User:UserOrgService');
    }

    /**
     * @return SurveyMemberService
     */
    protected function getSurveyMemberService()
    {
        return $this->createService('SurveyPlugin:Survey:SurveyMemberService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\Impl\MemberServiceImpl
     */
    protected function getProjectPlanMemberService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:MemberService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\Impl\ProjectPlanServiceImpl
     */
    protected function getProjectPlanService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:ProjectPlanService');
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
    protected function getOfflineCourseTaskService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineCourse:TaskService');
    }
}
