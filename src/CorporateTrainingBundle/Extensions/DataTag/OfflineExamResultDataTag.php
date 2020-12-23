<?php

namespace CorporateTrainingBundle\Extensions\DataTag;

use AppBundle\Extensions\DataTag\CourseBaseDataTag;
use AppBundle\Extensions\DataTag\DataTag;

class OfflineExamResultDataTag extends CourseBaseDataTag implements DataTag
{
    public function getData(array $arguments)
    {
        $userId = $this->getCurrentUser()->getId();
        $examResult = $this->getOfflineExamMemberService()->getMemberByOfflineExamIdAndUserId($arguments['examId'], $userId);

        return $examResult;
    }

    protected function getOfflineExamMemberService()
    {
        return $this->getServiceKernel()->createService('CorporateTrainingBundle:OfflineExam:MemberService');
    }
}
