<?php

namespace CorporateTrainingBundle\Biz\Focus\Strategy\Impl;

use AppBundle\Common\ArrayToolkit;
use CorporateTrainingBundle\Biz\Focus\Strategy\BaseFocusStrategy;
use CorporateTrainingBundle\Biz\Focus\Strategy\FocusStrategy;
use CorporateTrainingBundle\Biz\OfflineActivity\Service\OfflineActivityService;

class OfflineActivityFocusStrategyImpl extends BaseFocusStrategy implements FocusStrategy
{
    public function findFocusAgo($type = 'my', $time)
    {
        $startTime = mktime(0, 0, 0, date('m', $time), date('d', $time), date('Y', $time));
        $offlineActivityIds = $this->findFocusOfflineActivityIds($type);

        if (empty($offlineActivityIds)) {
            return array();
        }

        $conditions = array(
            'ids' => $offlineActivityIds,
            'endTime_LT' => $time,
            'endTime_GE' => $startTime,
        );

        $offlineActivity = $this->getOfflineActivityService()->searchOfflineActivities(
            $conditions,
            array('endTime' => 'DESC'),
            0,
            PHP_INT_MAX
        );

        return $offlineActivity;
    }

    public function findFocusNow($type = 'my', $time)
    {
        $offlineActivityIds = $this->findFocusOfflineActivityIds($type);

        if (empty($offlineActivityIds)) {
            return array();
        }

        $conditions = array(
            'ids' => $offlineActivityIds,
            'startTime_LE' => $time,
            'endTime_GT' => $time,
        );

        $offlineActivity = $this->getOfflineActivityService()->searchOfflineActivities(
            $conditions,
            array('endTime' => 'ASC'),
            0,
            5
        );

        return $offlineActivity;
    }

    public function findFocusLater($type = 'my', $time)
    {
        $endTime = mktime(0, 0, 0, date('m', $time), date('d', $time) + 1, date('Y', $time)) - 1;
        $offlineActivityIds = $this->findFocusOfflineActivityIds($type);

        if (empty($offlineActivityIds)) {
            return array();
        }

        $conditions = array(
            'ids' => $offlineActivityIds,
            'startTime_GE' => $time,
            'startTime_LT' => $endTime,
        );

        $offlineActivity = $this->getOfflineActivityService()->searchOfflineActivities(
            $conditions,
            array('startTime' => 'ASC'),
            0,
            5
        );

        return $offlineActivity;
    }

    public function findFocusByStartTimeAndEndTime($type = 'my', $startTime, $endTime)
    {
        $offlineActivityIds = $this->findFocusOfflineActivityIds($type);

        if (empty($offlineActivityIds)) {
            return array();
        }

        $conditions = array(
            'ids' => $offlineActivityIds,
            'endTime_GE' => $startTime,
            'startTime_LT' => $endTime,
        );

        return $this->getOfflineActivityService()->searchOfflineActivities(
            $conditions,
            array('id' => 'ASC'),
            0,
            PHP_INT_MAX
        );
    }

    protected function findFocusOfflineActivityIds($type)
    {
        $currentUser = $this->getCurrentUser();
        $offlineActivityIds = array();

        if ('my' == $type) {
            $offlineActivityIds = array_merge(
                $offlineActivityIds,
                $this->findMyCreateOfflineActivityIds()
            );
        }

        if (!$currentUser->hasPermission('admin_offline_activity') && !$currentUser->hasPermission('admin_data')) {
            $offlineActivityIds = array();
        }

        return $offlineActivityIds;
    }

    protected function findMyCreateOfflineActivityIds()
    {
        $currentUser = $this->getCurrentUser();
        $conditions = array(
            'creator' => $currentUser['id'],
            'status' => 'published',
        );

        $offlineActivities = $this->getOfflineActivityService()->searchOfflineActivities(
            $conditions,
            array('id' => 'ASC'),
            0,
            PHP_INT_MAX
        );

        return ArrayToolkit::column($offlineActivities, 'id');
    }

    /**
     * @return OfflineActivityService
     */
    protected function getOfflineActivityService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineActivity:OfflineActivityService');
    }
}
