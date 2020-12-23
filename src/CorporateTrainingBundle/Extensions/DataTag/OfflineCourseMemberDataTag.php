<?php

namespace CorporateTrainingBundle\Extensions\DataTag;

use AppBundle\Extensions\DataTag\CourseBaseDataTag;
use AppBundle\Extensions\DataTag\DataTag;

class OfflineCourseMemberDataTag extends CourseBaseDataTag implements DataTag
{
    public function getData(array $arguments)
    {
        $offlineCourseMember = $this->getOfflineCourseMemberService()->getMemberByOfflineCourseIdAndUserId($arguments['id'], $arguments['userId']);

        return $offlineCourseMember;
    }

    protected function getOfflineCourseMemberService()
    {
        return $this->getServiceKernel()->createService('CorporateTrainingBundle:OfflineCourse:MemberService');
    }
}
