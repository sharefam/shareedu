<?php

namespace CorporateTrainingBundle\Extensions\DataTag;

use AppBundle\Extensions\DataTag\BaseDataTag;
use AppBundle\Extensions\DataTag\DataTag;
use CorporateTrainingBundle\Biz\OfflineActivity\Service\MemberService;

class OfflineActivityMemberExistDataTag extends BaseDataTag implements DataTag
{
    public function getData(array $arguments)
    {
        return $this->getMemberService()->isMember($arguments['offlineActivityId'], $arguments['userId']);
    }

    /**
     * @return MemberService
     */
    protected function getMemberService()
    {
        return $this->getServiceKernel()->getBiz()->service('CorporateTrainingBundle:OfflineActivity:MemberService');
    }
}
