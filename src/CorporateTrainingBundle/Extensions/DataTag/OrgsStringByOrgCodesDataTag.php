<?php

namespace CorporateTrainingBundle\Extensions\DataTag;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Extensions\DataTag\BaseDataTag;
use AppBundle\Extensions\DataTag\DataTag;
use CorporateTrainingBundle\Biz\Org\Service\OrgService;

class OrgsStringByOrgCodesDataTag extends BaseDataTag implements DataTag
{
    public function getData(array $arguments)
    {
        if (!isset($arguments['orgCodes'])) {
            throw new \InvalidArgumentException($this->getServiceKernel()->trans('OrgCodes参数缺失'));
        }

        if (empty($arguments['orgCodes'])) {
            return null;
        }

        $orgs = $this->getOrgService()->searchOrgs(
            array('orgCodes' => $arguments['orgCodes']),
            array('depth' => 'ASC'),
            0,
            PHP_INT_MAX
        );

        $orgNamesString = implode(',', ArrayToolkit::column($orgs, 'name'));
        $orgCodesString = implode(',', ArrayToolkit::column($orgs, 'orgCode'));
        $orgIdsString = implode(',', ArrayToolkit::column($orgs, 'id'));

        return array(
            'namesString' => $orgNamesString,
            'orgCodesString' => $orgCodesString,
            'orgIdsString' => $orgIdsString,
        );
    }

    /**
     * @return OrgService
     */
    public function getOrgService()
    {
        return $this->getServiceKernel()->createService('Org:OrgService');
    }
}
