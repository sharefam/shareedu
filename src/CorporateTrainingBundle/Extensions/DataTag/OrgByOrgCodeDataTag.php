<?php

namespace CorporateTrainingBundle\Extensions\DataTag;

use AppBundle\Extensions\DataTag\BaseDataTag;
use AppBundle\Extensions\DataTag\DataTag;
use CorporateTrainingBundle\Biz\Org\Service\OrgService;

class OrgByOrgCodeDataTag extends BaseDataTag implements DataTag
{
    public function getData(array $arguments)
    {
        if (!isset($arguments['orgCode'])) {
            throw new \InvalidArgumentException($this->getServiceKernel()->trans('OrgCode参数缺失'));
        }

        return $this->getOrgService()->getOrgByOrgCode($arguments['orgCode']);
    }

    /**
     * @return OrgService
     */
    public function getOrgService()
    {
        return $this->getServiceKernel()->createService('Org:OrgService');
    }
}
