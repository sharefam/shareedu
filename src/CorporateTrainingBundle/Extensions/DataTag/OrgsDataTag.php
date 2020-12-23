<?php

namespace CorporateTrainingBundle\Extensions\DataTag;

use AppBundle\Extensions\DataTag\DataTag;
use AppBundle\Extensions\DataTag\BaseDataTag;
use CorporateTrainingBundle\Common\OrgToolkit;

class OrgsDataTag extends BaseDataTag implements DataTag
{
    public function getData(array $arguments)
    {
        if (!isset($arguments['orgIds'])) {
            throw new \InvalidArgumentException($this->getServiceKernel()->trans('OrgIds参数缺失'));
        }

        $orgs = $this->getOrgService()->findOrgsByIds($arguments['orgIds']);
        $org = reset($orgs);

        $orgs['orgNames'] = OrgToolkit::buildOrgsNames($arguments['orgIds'], $orgs);
        $orgs['orgName'] = empty($org) ? '' : $org['name'];

        return $orgs;
    }

    public function getOrgService()
    {
        return $this->getServiceKernel()->getBiz()->service('CorporateTrainingBundle:Org:OrgService');
    }
}
