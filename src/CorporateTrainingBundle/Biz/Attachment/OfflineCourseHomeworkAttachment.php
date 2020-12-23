<?php

namespace CorporateTrainingBundle\Biz\Attachment;

class OfflineCourseHomeworkAttachment extends BaseAttachment
{
    public function canDelete($fileId)
    {
        $user = $this->getCurrentUser();
        $fileUsed = $this->getUploadFileService()->getUseFile($fileId);
        if (empty($fileUsed)) {
            return true;
        }
        $taskResult = $this->getOfflineCourseTaskService()->getTaskResult($fileUsed['targetId']);

        if ('projectPlaning.offline.homework' != $fileUsed['targetType']) {
            return false;
        }

        if ($user['id'] == $taskResult['userId']) {
            return true;
        }

        return false;
    }

    public function canOperate($fileId, $type = '')
    {
        $user = $this->getCurrentUser();
        $fileUsed = $this->getUploadFileService()->getUseFile($fileId);
        $taskResult = $this->getOfflineCourseTaskService()->getTaskResult($fileUsed['targetId']);

        if ('projectPlaning.offline.homework' != $fileUsed['targetType']) {
            return false;
        }

        if ($this->canMangeOfflineCourse($taskResult['offlineCourseId'])) {
            return true;
        }

        if ($user['id'] == $taskResult['userId'] && 'download' != $type) {
            return true;
        }

        return false;
    }

    protected function canMangeOfflineCourse($offlineCourseId)
    {
        return $this->getOfflineCourseService()->hasOfflineCourseManageRole($offlineCourseId);
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

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\Impl\ProjectPlanServiceImpl
     */
    protected function getProjectPlanService()
    {
        return $this->createService('ProjectPlan:ProjectPlanService');
    }
}
