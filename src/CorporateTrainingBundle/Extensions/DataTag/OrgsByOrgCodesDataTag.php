<?php

namespace CorporateTrainingBundle\Extensions\DataTag;

use AppBundle\Extensions\DataTag\BaseDataTag;
use AppBundle\Extensions\DataTag\DataTag;
use CorporateTrainingBundle\Biz\Org\Service\OrgService;

class OrgsByOrgCodesDataTag extends BaseDataTag implements DataTag
{
    public function getData(array $arguments)
    {
        if (!isset($arguments['orgCodes'])) {
            throw new \InvalidArgumentException($this->getServiceKernel()->trans('OrgCodes参数缺失'));
        }

        $orgs = $this->getOrgService()->searchOrgs(
            array('orgCodes' => $arguments['orgCodes']),
            array('depth' => 'ASC'),
            0,
            PHP_INT_MAX
        );
        $orgs = $this->transformOrgCode($orgs);

        return $orgs;
    }

    protected function transformOrgCode($orgs)
    {
        foreach ($orgs as &$org) {
            $org['orgCodeTransformed'] = str_replace('.', '-', substr($org['orgCode'], 0, strlen($org['orgCode']) - 1));
        }

        return $orgs;
    }

    /**
     * @return OrgService
     */
    public function getOrgService()
    {
        return $this->getServiceKernel()->createService('Org:OrgService');
    }
}
