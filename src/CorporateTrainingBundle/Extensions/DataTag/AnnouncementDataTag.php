<?php

namespace CorporateTrainingBundle\Extensions\DataTag;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Extensions\DataTag\BaseDataTag;
use AppBundle\Extensions\DataTag\DataTag;
use Biz\Announcement\Service\AnnouncementService;
use CorporateTrainingBundle\Biz\Org\Service\OrgService;

class AnnouncementDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取用户所属组织机构及以上的公告列表.
     *
     * 可传入的参数：
     *   count    必需 取值不超过100
     *
     * @param array $arguments 参数
     *
     * @return array 公告列表
     */
    public function getData(array $arguments)
    {
        $this->checkCount($arguments);

        // 以每10分钟为单位，以免Cache开启的时候，避免每次请求都要查询数据库
        $currentTime = strtotime(date('Y-m-d H').':'.(intval(date('i') / 10) * 10).':0');

        $conditions = array('targetType' => 'global', 'startTime' => $currentTime, 'endTime' => $currentTime);

        $orgCodes = $this->getCurrentUser()->getOrgCodes();
        $orgIds = array();

        foreach ($orgCodes as &$orgCode) {
            $matchedOrgs = $this->getOrgService()->findSelfAndParentOrgsByOrgCode($orgCode);
            $orgId = ArrayToolkit::column($matchedOrgs, 'id');
            $orgIds = array_merge($orgId, $orgIds);
        }
        $conditions['orgIds'] = $orgIds;

        return $this->getAnnouncementService()->searchAnnouncements(
            $conditions,
            array('createdTime' => 'DESC'),
            0,
            $arguments['count']
        );
    }

    protected function checkCount(array $arguments)
    {
        if (empty($arguments['count'])) {
            throw new \InvalidArgumentException($this->getServiceKernel()->trans('count参数缺失'));
        }
        if ($arguments['count'] > 100) {
            throw new \InvalidArgumentException($this->getServiceKernel()->trans('count参数超出最大取值范围'));
        }
    }

    /**
     * @return AnnouncementService
     */
    protected function getAnnouncementService()
    {
        return $this->getServiceKernel()->createService('Announcement:AnnouncementService');
    }

    /**
     * @return OrgService
     */
    protected function getOrgService()
    {
        return $this->getServiceKernel()->createService('CorporateTrainingBundle:Org:OrgService');
    }
}
