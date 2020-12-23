<?php

namespace CorporateTrainingBundle\Controller;

use CorporateTrainingBundle\Biz\PostCourse\Service\PostCourseService;
use CorporateTrainingBundle\Biz\PostCourse\Service\UserPostCourseService;
use CorporateTrainingBundle\Biz\ProjectPlan\Service\MemberService;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\TaskController as BaseTaskController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Codeages\Biz\Framework\Service\Exception\AccessDeniedException as ServiceAccessDeniedException;
use Topxia\Service\Common\ServiceKernel;

class TaskController extends BaseTaskController
{
    public function showAction(Request $request, $courseId, $id)
    {
        $preview = $request->query->get('preview');

        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            return $this->createMessageResponse('info', ServiceKernel::instance()->trans('task.show.message.login'), '', 3, $this->generateUrl('login'));
        }

        $course = $this->getCourseService()->getCourse($courseId);

        $member = $this->getCourseMemberService()->getCourseMember($courseId, $user['id']);

        /*
         * 内训版：岗位课程或培训项目或专题直接加入
         */
        $canAutoJoin = $this->getCourseService()->canUserAutoJoinCourse($user, $course['id']);

        if ($canAutoJoin) {
            $member = $this->getCourseMemberService()->becomeStudent($courseId, $user['id']);
        }

        try {
            $task = $this->tryLearnTask($courseId, $id, (bool) $preview);
            $activity = $this->getActivityService()->getActivity($task['activityId'], true);

            if (!empty($activity['ext']) && !empty($activity['ext']['mediaId'])) {
                $media = $this->getUploadFileService()->getFile($activity['ext']['mediaId']);
            }

            $media = !empty($media) ? $media : array();
        } catch (AccessDeniedException $accessDeniedException) {
            return $this->handleAccessDeniedException($accessDeniedException, $request, $id);
        } catch (ServiceAccessDeniedException $deniedException) {
            return $this->handleAccessDeniedException($deniedException, $request, $id);
        }

        if ($member['locked']) {
            return $this->redirectToRoute('my_course_show', array('id' => $courseId));
        }

        if ($this->isCourseExpired($course) && !$this->getCourseService()->hasCourseManagerRole($course['id'])) {
            return $this->redirectToRoute('course_show', array('id' => $courseId));
        }

        if (null !== $member && 'teacher' != $member['role'] && !$this->getCourseMemberService()->isMemberNonExpired(
                $course,
                $member
            )
        ) {
            return $this->redirect($this->generateUrl('my_course_show', array('id' => $courseId)));
        }

        $activityConfig = $this->getActivityConfigByTask($task);

        if (null !== $member && 'student' === $member['role'] && $activityConfig->allowTaskAutoStart($task)) {
            $this->getTaskService()->trigger(
                $task['id'],
                'start',
                array(
                    'taskId' => $task['id'],
                )
            );
        }

        $taskResult = $this->getTaskResultService()->getUserTaskResultByTaskId($id);
        if (empty($taskResult)) {
            $taskResult = array('status' => 'none');
        }

        if ('finish' == $taskResult['status']) {
            $progress = $this->getLearningDataAnalysisService()->getUserLearningProgress($courseId, $user['id']);
            $finishedRate = $progress['percent'];
        }
        list($previousTask, $nextTask) = $this->getPreviousTaskAndTaskResult($task);

        $isCourseTeacher = false;
        if (in_array($user['id'], $course['teacherIds'])) {
            $isCourseTeacher = true;
        }
        $this->getTaskService()->freshTaskLearnStat($task['id']);

        return $this->render(
            'task/show.html.twig',
            array(
                'course' => $course,
                'member' => $member,
                'task' => $task,
                'taskResult' => $taskResult,
                'nextTask' => $nextTask,
                'previousTask' => $previousTask,
                'finishedRate' => empty($finishedRate) ? 0 : $finishedRate,
                'isCourseTeacher' => $isCourseTeacher,
                'allowEventAutoTrigger' => $activityConfig->allowEventAutoTrigger(),
                'media' => $media,
            )
        );
    }

    protected function canStartTask($task)
    {
        $activity = $this->getActivityService()->getActivity($task['activityId']);
        $config = $this->getActivityService()->getActivityConfig($activity['mediaType']);

        return $config->allowTaskAutoStart($activity);
    }

    /**
     * @return PostCourseService
     */
    protected function getPostCourseService()
    {
        return $this->createService('CorporateTrainingBundle:PostCourse:PostCourseService');
    }

    /**
     * @return UserPostCourseService
     */
    protected function getUserPostCourseService()
    {
        return $this->createService('CorporateTrainingBundle:PostCourse:UserPostCourseService');
    }

    /**
     * @return MemberService
     */
    protected function getProjectPlanMemberService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:MemberService');
    }
}
