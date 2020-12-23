<?php

namespace CorporateTrainingBundle\Extensions\DataTag;

use AppBundle\Extensions\DataTag\DataTag;
use AppBundle\Extensions\DataTag\BaseDataTag;

class LatestOfflineActivitiesDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取最新课程列表.
     *
     * 可传入的参数：
     *   categoryId 可选 分类ID
     *   notFree 可选 1：代表不包括免费课程 0：代表包括 默认包括
     *   count    必需 课程数量，取值不能超过100
     *
     * @param array $arguments 参数
     *
     * @return array 课程列表
     */
    public function getData(array $arguments)
    {
        $conditions = array();
        $conditions['status'] = 'published';

        $offlineActivities = $this->getOfflineActivityService()->searchOfflineActivities(
            $conditions,
            array('createdTime' => 'DESC'),
            0,
            $arguments['count']
        );

        return $offlineActivities;
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineActivity\Service\Impl\OfflineActivityServiceImpl
     */
    protected function getOfflineActivityService()
    {
        return $this->getServiceKernel()->createService('CorporateTrainingBundle:OfflineActivity:OfflineActivityService');
    }
}
