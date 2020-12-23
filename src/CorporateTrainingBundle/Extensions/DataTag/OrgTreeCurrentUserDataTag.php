<?php

namespace CorporateTrainingBundle\Extensions\DataTag;

use AppBundle\Extensions\DataTag\BaseDataTag;
use AppBundle\Extensions\DataTag\DataTag;
use CorporateTrainingBundle\Biz\Org\Service\OrgService;

class OrgTreeCurrentUserDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取组织机构的树的数据
     * 默认根据用户所在的组织机构去选择.
     */
    public function getData(array $arguments)
    {
        $orgCodes = isset($arguments['orgCodes']) ? $arguments['orgCodes'] : array();

        if (empty($orgCodes)) {
            return $this->getOrgService()->findOrgsByPrefixOrgCodes();
        }

        return $this->getOrgService()->getVisibleOrgTreeDataByOrgCodes($orgCodes);
    }

    /**
     * @return OrgService
     */
    public function getOrgService()
    {
        return $this->getServiceKernel()->createService('Org:OrgService');
    }
}
