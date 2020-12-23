<?php

namespace CorporateTrainingBundle\Extensions\DataTag;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Extensions\DataTag\DataTag;
use AppBundle\Extensions\DataTag\BaseDataTag;
use CorporateTrainingBundle\Biz\ResourceUsePermissionShared\Service\ResourceUsePermissionSharedService;

class ResourceUsePermissionSharedCountDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取资源授权人数.
     *
     * @param array $arguments 参数
     *
     * @return int 人数
     */
    public function getData(array $arguments)
    {
        if (!ArrayToolkit::requireds($arguments, array('resourceType', 'resourceId'))) {
            throw new \InvalidArgumentException($this->getServiceKernel()->trans('参数缺失'));
        }

        return $this->getResourceUsePermissionSharedService()->countSharedRecords(array('resourceType' => $arguments['resourceType'], 'resourceId' => $arguments['resourceId']));
    }

    /**
     * @return ResourceUsePermissionSharedService
     */
    protected function getResourceUsePermissionSharedService()
    {
        return $this->getServiceKernel()->getBiz()->service('ResourceUsePermissionShared:ResourceUsePermissionSharedService');
    }
}
