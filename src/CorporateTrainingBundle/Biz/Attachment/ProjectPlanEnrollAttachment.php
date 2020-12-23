<?php

namespace CorporateTrainingBundle\Biz\Attachment;

class ProjectPlanEnrollAttachment extends BaseAttachment
{
    public function canOperate($fileId, $type = '')
    {
        $user = $this->getCurrentUser();
        $fileUsed = $this->getUploadFileService()->getUseFileByFileId($fileId);
        if (empty($fileUsed) && 'delete' == $type) {
            return true;
        }
        $record = $this->getProjectPlanMemberService()->getEnrollmentRecord($fileUsed['targetId']);

        if (!in_array($fileUsed['targetType'], array('project.plan.enroll', 'projectPlaning.material1', 'projectPlaning.material2', 'projectPlaning.material3'))) {
            return false;
        }

        if ($this->canManageProjectPlan($record['projectPlanId'])) {
            return true;
        }

        if ($user['id'] == $record['userId'] && 'download' != $type) {
            return true;
        }

        return false;
    }

    public function canManageProjectPlan($projectPlanId)
    {
        return $this->getProjectPlanService()->canManageProjectPlan($projectPlanId);
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\Impl\MemberServiceImpl
     */
    protected function getProjectPlanMemberService()
    {
        return $this->createService('ProjectPlan:MemberService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\Impl\ProjectPlanServiceImpl
     */
    protected function getProjectPlanService()
    {
        return $this->createService('ProjectPlan:ProjectPlanService');
    }
}
