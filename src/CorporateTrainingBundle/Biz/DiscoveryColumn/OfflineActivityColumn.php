<?php

namespace CorporateTrainingBundle\Biz\DiscoveryColumn;

use CorporateTrainingBundle\Biz\OfflineActivity\Service\OfflineActivityService;

class OfflineActivityColumn extends BaseColumn
{
    public function buildColumn($column)
    {
        $condition = array(
            'status' => 'published',
            'requireAudit' => 1,
            'enrollmentEndDate_GE' => time(),
        );
        $condition['ids'] = $this->getResourceVisibleScopeService()->findVisibleResourceIdsByResourceTypeAndUserId('offlineActivity', $this->getCurrentUser()->getId());

        $offlineActivities = $this->getOfflineActivityService()->searchOfflineActivities(
            $condition,
            array('enrollmentEndDate' => 'ASC'),
            0,
            $column['showCount']
        );

//        TODO  兼容app页面字段，后期app修改后删除
        foreach ($offlineActivities as &$offlineActivity) {
            $offlineActivity['startDate'] = $offlineActivity['startTime'];
            $offlineActivity['endDate'] = $offlineActivity['endTime'];
        }

        $column['data'] = $this->buildColumnsCategoryName($offlineActivities);
        $column['actualCount'] = count($offlineActivities);

        return $column;
    }

    /**
     * @return OfflineActivityService
     */
    protected function getOfflineActivityService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineActivity:OfflineActivityService');
    }
}
