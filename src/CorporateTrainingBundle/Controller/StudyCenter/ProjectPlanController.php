<?php

namespace CorporateTrainingBundle\Controller\StudyCenter;

use AppBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;

class ProjectPlanController extends BaseController
{
    public function homeworkSubmitAction(Request $request, $offlineCourseTaskId)
    {
        $user = $this->getCurrentUser();
        $taskResult = $this->getOfflineCourseTaskService()->getTaskResultByTaskIdAndUserId($offlineCourseTaskId, $user['id']);
        $task = $this->getOfflineCourseTaskService()->getTask($offlineCourseTaskId);

        if (empty($taskResult)) {
            $fields = array(
                'offlineCourseId' => $task['offlineCourseId'],
                'taskId' => $offlineCourseTaskId,
                'userId' => $user['id'],
            );

            $taskResult = $this->getOfflineCourseTaskService()->createTaskResult($fields);
        }
        $offlineCourseMember = $this->getofflineCourseMemberService()->getMemberByOfflineCourseIdAndUserId($taskResult['offlineCourseId'], $taskResult['userId']);

        if (empty($offlineCourseMember)) {
            $this->getOfflineCourseMemberService()->createMember(array('offlineCourseId' => $taskResult['offlineCourseId'], 'userId' => $taskResult['userId']));
        }
        if ($request->getMethod() === 'POST') {
            $attachment = $request->request->get('attachment');
            $file = $this->getUploadFileService()->getFile($attachment['fileIds']);

            if (!empty($file)) {
                $this->getOfflineCourseTaskService()->submitHomework($taskResult, $attachment);

                return $this->createJsonResponse(array('success' => true, 'message' => ServiceKernel::instance()->trans('study_center.project_plan.homework_submit_success')));
            }

            return $this->createJsonResponse(array('success' => false, 'message' => ServiceKernel::instance()->trans('study_center.project_plan.homework_submit_error')));
        }

        return $this->render('study-center/my-task/project-plan/homework-submit-modal.html.twig',
            array(
                'offlineCourseTaskId' => $offlineCourseTaskId,
                'homeworkResult' => $taskResult,
                'task' => $task,
            ));
    }

    public function homeworkAttachmentDeleteAction(Request $request, $id)
    {
        $previewType = $request->query->get('type', 'attachment');
        $fileUsed = $this->getUploadFileService()->getUseFile($id);
        $attachment = $this->getUploadFileService()->getFile($fileUsed['fileId']);

        $user = $this->getCurrentUser();

        if ($previewType == 'attachment' && ($user->isAdmin() || $user['id'] == $attachment['createdUserId'])) {
            $this->getUploadFileService()->deleteFile($attachment['id']);
            $result = $this->getOfflineCourseTaskService()->getTask($fileUsed['targetId']);
            $this->getOfflineCourseTaskService()->updateTaskResult($result['id'], array('homeworkStatus' => 'unsubmit'));
        }

        return $this->createJsonResponse(array('msg' => 'ok'));
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\ProjectPlanService
     */
    protected function getProjectPlanService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:ProjectPlanService');
    }

    /**
     * @return \Biz\File\Service\UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineCourse\Service\OfflineCourseService
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

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineCourse\Service\Impl\MemberServiceImpl
     */
    protected function getOfflineCourseMemberService()
    {
        return $this->getBiz()->service('CorporateTrainingBundle:OfflineCourse:MemberService');
    }
}
