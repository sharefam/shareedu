<?php

namespace CorporateTrainingBundle\Controller\OfflineCourse;

use AppBundle\Common\Exception\AccessDeniedException;
use AppBundle\Controller\BaseController;
use AppBundle\Common\ArrayToolkit;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;
use Psr\Log\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;

class OfflineCourseManageController extends BaseController
{
    public function headerAction($id)
    {
        $user = $this->getCurrentUser();

        if ($user->hasPermission('admin_data_center_project_plan')) {
            $offlineCourse = $this->getOfflineCourseService()->getOfflineCourse($id);
        } else {
            $offlineCourse = $this->getOfflineCourseService()->tryManageOfflineCourse($id);
        }

        $projectPlanItem = $this->getProjectPlanService()->getProjectPlanItemByProjectPlanIdAndTargetIdAndTargetType($offlineCourse['projectPlanId'], $id, 'offline_course');

        return $this->render('offline-course-manage/header.html.twig',
            array(
                'offlineCourse' => $offlineCourse,
                'projectPlanId' => $projectPlanItem['projectPlanId'],
            )
        );
    }

    public function baseAction(Request $request, $id)
    {
        $offlineCourse = $this->getOfflineCourseService()->tryManageOfflineCourse($id);

        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $offlineCourse = $this->getOfflineCourseService()->updateOfflineCourse($offlineCourse['id'], $data);
            $teacherIds = ArrayToolkit::column(json_decode($data['teachers'], true), 'id');
            $this->getOfflineCourseService()->setTeachers($offlineCourse['id'], $teacherIds);

            return $this->redirectToRoute(
                'training_offline_course_manage_base',
                array('id' => $id)
            );
        }

        $teacherId = $this->getTeacherId($offlineCourse['teacherIds'][0]);

        return $this->render('offline-course-manage/base.html.twig',
            array(
                'offlineCourse' => $offlineCourse,
                'course' => $offlineCourse,
                'teacherId' => $teacherId,
            )
        );
    }

    public function tasksAction(Request $request, $id)
    {
        $offlineCourse = $this->getOfflineCourseService()->tryManageOfflineCourse($id);
        $tasks = $this->getOfflineCourseTaskService()->searchTasks(
            array('offlineCourseId' => $id),
            array('seq' => 'ASC'),
            0,
            PHP_INT_MAX
        );

        return $this->render(
            'offline-course-manage/tasks.html.twig',
            array(
            'offlineCourse' => $offlineCourse,
            'tasks' => $tasks,
            )
        );
    }

    public function createTaskAction(Request $request, $id, $type)
    {
        $this->getOfflineCourseService()->tryManageOfflineCourse($id);

        if ('POST' == $request->getMethod()) {
            $fields = $request->request->all();
            $task = $this->buildParam($fields);
            $task = $this->parseTimeFields($task);
            $task['offlineCourseId'] = $id;
            $task['type'] = $type;
            $task['creator'] = $this->getCurrentUser()->getId();
            if (empty($task['homeworkDeadline'])) {
                $task['homeworkDeadline'] = 0;
            }
            if ('questionnaire' == $type) {
                $questionnaire = $this->getQuestionnaireService()->getQuestionnaire($task['mediaId']);
                $hasPermission = $this->getManagePermissionService()->checkResourceUsePermission('pluginQuestionnaire', $task['mediaId'], $questionnaire['orgId']);
                if (!$hasPermission) {
                    throw new AccessDeniedException('questionnaire Not Use Permission');
                }
            }

            $this->getOfflineCourseTaskService()->createTask($task);

            return $this->redirect($this->generateUrl('training_offline_course_manage_tasks', array(
                'id' => $id,
            )));
        }

        $trainingActivities = $this->getTrainingActivities();

        return $this->forward(
            $trainingActivities[$type]['controller'].':create',
            array(
                'id' => $id,
                'type' => $type,
            )
        );
    }

    public function editTaskAction(Request $request, $courseId, $taskId)
    {
        $this->getOfflineCourseService()->tryManageOfflineCourse($courseId);
        $task = $this->getOfflineCourseTaskService()->getTask($taskId);

        if ($task['offlineCourseId'] != $courseId) {
            throw new InvalidArgumentException('The task is not in the plan');
        }

        if ('POST' == $request->getMethod()) {
            $fields = $request->request->all();
            $fields['activityId'] = $task['activityId'];
            $fields = $this->buildParam($fields);
            $fields['hasHomework'] = $task['hasHomework'];
            $this->getOfflineCourseTaskService()->updateTask($taskId, $this->parseTimeFields($fields));

            return $this->redirect($this->generateUrl('training_offline_course_manage_tasks', array(
                'id' => $courseId,
            )));
        }

        $activity = $this->getActivityService()->getActivity($task['activityId'], true);

        $trainingActivities = $this->getTrainingActivities();

        return $this->forward(
            $trainingActivities[$task['type']]['controller'].':edit',
            array(
                'task' => $task,
                'activity' => $activity,
            )
        );
    }

    public function deleteTaskAction(Request $request, $courseId, $taskId)
    {
        $offlineCourse = $this->getOfflineCourseService()->tryManageOfflineCourse($courseId);

        $task = $this->getOfflineCourseTaskService()->getTask($taskId);

        if (empty($task)) {
            throw new NotFoundException('Task does not exist');
        }

        if ($task['offlineCourseId'] != $courseId) {
            throw new InvalidArgumentException('The task is not in the plan');
        }

        $this->getOfflineCourseTaskService()->deleteTask($taskId);

        return $this->createJsonResponse(array('success' => true));
    }

    protected function getTrainingActivities()
    {
        return $this->container->get('corporatetraining.activity.extension')->getTrainingActivities();
    }

    public function matchSurveyAction(Request $request)
    {
        $queryField = $request->query->get('q');
        $conditions = array(
            'nameLike' => $queryField,
            'status' => 'published',
            'type' => 'feedback',
        );
        $conditions['orgIds'] = $this->prepareOrgIds($conditions);
        $questionnaires = $this->getQuestionnaireService()->searchQuestionnaires(
            $conditions,
            array('createdTime' => 'DESC'),
            0,
            10
        );

        $teacherQuestionnaires = array();

        foreach ($questionnaires as $questionnaire) {
            $teacherQuestionnaires[] = array(
                'id' => $questionnaire['id'],
                'name' => $questionnaire['name'],
            );
        }

        return $this->createJsonResponse($teacherQuestionnaires);
    }

    public function teachersMatchAction(Request $request)
    {
        $queryField = $request->query->get('q');

        $users = $this->getUserService()->searchUsers(
            array('truename' => $queryField, 'roles' => 'ROLE_TEACHER'),
            array('createdTime' => 'DESC'),
            0,
            10
        );

        $teachers = array();

        foreach ($users as $user) {
            $userProfile = $this->getUserService()->getUserProfile($user['id']);
            $teachers[] = array(
                'id' => $user['id'],
                'nickname' => $user['nickname'],
                'truename' => $userProfile['truename'],
                'avatar' => $this->getWebExtension()->avatarPath($user, 'small'),
                'isVisible' => '1',
            );
        }

        return $this->createJsonResponse($teachers);
    }

    public function viewSignQrcodeAction(Request $request, $taskId)
    {
        $user = $this->getCurrentUser();
        $task = $this->getOfflineCourseTaskService()->getTask($taskId);
        $offlineCourse = $this->getOfflineCourseService()->tryManageOfflineCourse($task['offlineCourseId']);

        $qrcodeImgUrl = $this->qrcode($taskId);

        return $this->render('offline-course/sign-in/qr-code.html.twig',
            array(
                'course' => $offlineCourse,
                'task' => $task,
                'qrcodeImgUrl' => $qrcodeImgUrl,
            )
        );
    }

    public function sortTaskAction(Request $request)
    {
        $ids = $request->request->get('ids');

        if (!empty($ids)) {
            $this->getOfflineCourseTaskService()->sortTasks($ids);
        }

        return $this->createJsonResponse(true);
    }

    protected function qrcode($taskId)
    {
        $token = $this->getTokenService()->makeToken('qrcode', array(
            'data' => array(
                'url' => $this->generateUrl('project_plan_offline_course_sign_in', array('taskId' => $taskId), true),
            ),
            'duration' => 3600 * 24 * 365,
        ));

        $url = $this->generateUrl('common_parse_qrcode', array('token' => $token['token']), true);

        return $this->generateUrl('common_qrcode', array('text' => $url), true);
    }

    protected function getTeacherId($teacherId)
    {
        $teacher = $this->getUserService()->getUser($teacherId);
        $teacherProfile = $this->getUserService()->getUserProfile($teacherId);
        $teacherId = array();

        $teacherId[] = array(
            'id' => $teacher['id'],
            'nickname' => $teacher['nickname'],
            'truename' => $teacherProfile['truename'],
            'isVisible' => '1',
            'avatar' => $this->get('web.twig.extension')->avatarPath($teacher, 'small'),
        );

        return $teacherId;
    }

    protected function buildParam($task)
    {
        if ('offlineCourse' == $task['mediaType']) {
            if (isset($task['hasHomework']) && 'on' == $task['hasHomework']) {
                $task['hasHomework'] = 1;
            } else {
                $task['hasHomework'] = 0;
            }
        }

        return $task;
    }

    protected function parseTimeFields($fields)
    {
        if (!empty($fields['startTime'])) {
            $fields['startTime'] = strtotime($fields['startTime']);
        }
        if (!empty($fields['endTime'])) {
            $fields['endTime'] = strtotime($fields['endTime']);
        }

        if (!empty($fields['homeworkDeadline'])) {
            $fields['homeworkDeadline'] = strtotime($fields['homeworkDeadline']);
        }

        return $fields;
    }

    /**
     * @return \Biz\Activity\Service\Impl\ActivityServiceImpl
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    protected function getTokenService()
    {
        return $this->createService('User:TokenService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\Impl\ProjectPlanServiceImpl
     */
    protected function getProjectPlanService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:ProjectPlanService');
    }

    /**
     * @return \SurveyPlugin\Biz\Questionnaire\Service\Impl\QuestionnaireServiceImpl
     */
    protected function getQuestionnaireService()
    {
        return $this->createService('SurveyPlugin:Questionnaire:QuestionnaireService');
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
}
