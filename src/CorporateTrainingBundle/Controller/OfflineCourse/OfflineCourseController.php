<?php

namespace CorporateTrainingBundle\Controller\OfflineCourse;

use AppBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Endroid\QrCode\QrCode;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Service\Common\ServiceKernel;

class OfflineCourseController extends BaseController
{
    public function signInAction(Request $request, $taskId)
    {
        $task = $this->getOfflineCourseTaskService()->getTask($taskId);
        $course = $this->getOfflineCourseService()->getOfflineCourse($task['offlineCourseId']);
        $projectPlanItem = $this->getProjectPlanService()->getProjectPlanItemByTargetIdAndTargetType($course['id'], 'offline_course');

        if ($request->query->get('origin') == 'app') {
            $nickname = $request->query->get('nickname');
            $result = $this->signInApp($nickname, $task, $projectPlanItem['projectPlanId']);

            return $result;
        }

        if ($task['endTime'] < time()) {
            return $this->render('offline-course/sign-in/error.html.twig',
                array(
                    'message' => ServiceKernel::instance()->trans('offline_course.sign_in.message.end'),
                )
            );
        }

        if ('POST' === $request->getMethod()) {
            $field = $request->request->get('field');
            $user = $this->getUserService()->getUserByLoginField($field);

            if (empty($user)) {
                return $this->render('offline-course/sign-in/error.html.twig',
                    array(
                        'taskId' => $taskId,
                        'message' => ServiceKernel::instance()->trans('offline_course.sign_in.message.data_error'),
                    )
                );
            }

            $member = $this->getProjectPlanMemberService()->getProjectPlanMemberByUserIdAndProjectPlanId($user['id'], $projectPlanItem['projectPlanId']);
            if (empty($member)) {
                return $this->render('offline-course/sign-in/error.html.twig',
                    array(
                        'taskId' => $taskId,
                        'message' => ServiceKernel::instance()->trans('project_plan.can_do_exam.message.no_permission'),
                    )
                );
            }

            $taskResult = $this->getOfflineCourseTaskService()->getTaskResultByTaskIdAndUserId($task['id'], $user['id']);
            if (!empty($taskResult) && $taskResult['attendStatus'] == 'attended') {
                return $this->render('offline-course/sign-in/error.html.twig',
                    array(
                        'taskId' => $taskId,
                        'message' => ServiceKernel::instance()->trans('offline_course.sign_in.message.repeat'),
                    )
                );
            }

            $this->getOfflineCourseTaskService()->signIn($user['id'], $task);

            return $this->render('offline-course/sign-in/success.html.twig',
                array(
                    'taskId' => $taskId,
                    'message' => ServiceKernel::instance()->trans('offline_course.sign_in.success'),
                )
            );
        }

        return $this->render('offline-course/sign-in/index.html.twig',
            array(
                'task' => $task,
                'course' => $course,
            )
        );
    }

    public function viewSurveyQrcodeAction(Request $request, $taskId)
    {
        $task = $this->getOfflineCourseTaskService()->getTask($taskId);
        $course = $this->getOfflineCourseService()->getOfflineCourse($task['offlineCourseId']);
        $projectPlanItem = $this->getProjectPlanService()->getProjectPlanItemByTargetIdAndTargetType($course['id'], 'offline_course');
        $activity = $this->getActivityService()->getActivity($task['activityId']);

        return $this->render('offline-course/do-survey/qr-code.html.twig',
            array(
                'course' => $course,
                'task' => $task,
                'surveyId' => $activity['mediaId'],
                'projectPlanId' => $projectPlanItem['projectPlanId'],
            )
        );
    }

    public function doSurveyQrcodeAction(Request $request)
    {
        $surveyId = $request->query->get('surveyId');
        $projectPlanId = $request->query->get('projectPlanId');
        $taskId = $request->query->get('taskId');

        $url = $this->generateUrl('can_do_survey', array('surveyId' => $surveyId, 'projectPlanId' => $projectPlanId, 'taskId' => $taskId), true);
        $qrCode = new QrCode();
        $qrCode->setText($url);
        $img = $qrCode->get('png');

        $headers = array('Content-Type' => 'image/png',
            'Content-Disposition' => 'inline; filename="image.png"', );

        return new Response($img, 200, $headers);
    }

    public function canDoSurveyAction($surveyId, $projectPlanId, $taskId)
    {
        $user = $this->getCurrentUser();
        $member = $this->getProjectPlanMemberService()->getProjectPlanMemberByUserIdAndProjectPlanId($user['id'], $projectPlanId);

        if (empty($member)) {
            return $this->render('offline-course/do-survey/error.html.twig',
                array(
                    'message' => ServiceKernel::instance()->trans('project_plan.can_do_exam.message.no_permission'),
                )
            );
        }

        $offlineCourseTask = $this->getOfflineCourseTaskService()->getTask($taskId);
        $offlineCourse = $this->getOfflineCourseService()->getOfflineCourse($offlineCourseTask['offlineCourseId']);

        if (in_array($member['userId'], $offlineCourse['teacherIds'])) {
            return $this->render('offline-course/do-survey/error.html.twig',
                array(
                    'message' => ServiceKernel::instance()->trans('offline_course.sign_in.message.teacher_survey'),
                )
            );
        }

        $surveyMember = $this->getSurveyMemberService()->getMemberBySurveyIdAndUserId($surveyId, $user['id']);
        if (empty($surveyMember)) {
            $fields = array(
                'surveyId' => $surveyId,
                'userId' => $user['id'],
                'status' => 'doing',
            );
            $this->getSurveyMemberService()->createMember($fields);
        }

        $task = $this->getOfflineCourseTaskService()->getTask($taskId);
        $this->getOfflineCourseTaskService()->signIn($user['id'], $task);

        return $this->redirect(
            $this->generateUrl('survey_do', array('surveyId' => $surveyId))
        );
    }

    protected function signInApp($nickname, $task, $projectPlanId)
    {
        $user = $this->getUserService()->getUserByNickname($nickname);

        $member = $this->getProjectPlanMemberService()->getProjectPlanMemberByUserIdAndProjectPlanId($user['id'], $projectPlanId);
        if (empty($member)) {
            return $this->createJsonResponse(array('success' => false, 'message' => ServiceKernel::instance()->trans('project_plan.can_do_exam.message.no_permission')));
        }

        if ($task['endTime'] < time()) {
            return  $this->createJsonResponse(array('success' => false, 'message' => ServiceKernel::instance()->trans('offline_course.sign_in.message.end')));
        }

        $taskResult = $this->getOfflineCourseTaskService()->getTaskResultByTaskIdAndUserId($task['id'], $user['id']);
        if (!empty($taskResult) && $taskResult['attendStatus'] == 'attended') {
            return $this->createJsonResponse(array('success' => false, 'message' => ServiceKernel::instance()->trans('offline_course.sign_in.message.repeat')));
        }

        $this->getOfflineCourseTaskService()->signIn($user['id'], $task);

        return $this->createJsonResponse(array('success' => true, 'message' => ServiceKernel::instance()->trans('offline_course.sign_in.success')));
    }

    protected function getTokenService()
    {
        return $this->createService('User:TokenService');
    }

    /**
     * @return \SurveyPlugin\Biz\Survey\Service\Impl\SurveyMemberServiceImpl
     */
    protected function getSurveyMemberService()
    {
        return $this->createService('SurveyPlugin:Survey:SurveyMemberService');
    }

    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    protected function getCourseTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
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

    protected function getOfflineCourseAttendanceService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:OfflineCourseAttendanceService');
    }

    protected function getOfflineCourseHomeworkService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:OfflineCourseHomeworkService');
    }

    protected function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
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
