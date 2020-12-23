<?php

namespace CorporateTrainingBundle\Biz\Exporter;

use AppBundle\Common\ArrayToolkit;
use CorporateTrainingBundle\Biz\DataStatistics\Service\DataStatisticsService;
use CorporateTrainingBundle\Common\DateToolkit;

abstract class BaseDataStatisticsExporter extends AbstractExporter
{
    public function canExport($parameters)
    {
        return $this->hasManagePermission();
    }

    protected function hasManagePermission()
    {
        $currentUser = $this->biz['user'];
        if (!$currentUser->isLogin()) {
            return false;
        }

        return $currentUser->hasPermission('admin_data_online_course_data');
    }

    protected function getAvgLearnTime($userCount, $totalLearnTime)
    {
        if (empty($totalLearnTime) || empty($userCount)) {
            return 0;
        }

        return floor($totalLearnTime / $userCount);
    }

    protected function prepareSearchConditions($conditions)
    {
        $conditions['noType'] = 'system';
        $conditions['locked'] = 0;

        if (empty($conditions['courseCreatedTime'])) {
            list($startDateTime, $endDateTime) = DateToolkit::generateStartDateAndEndDate('year');
            $conditions['startDateTime'] = strtotime($startDateTime);
            $conditions['endDateTime'] = strtotime($endDateTime.' 23:59:59');
        } else {
            $date = explode('-', $conditions['courseCreatedTime']);
            $conditions['startDateTime'] = strtotime($date[0]);
            $conditions['endDateTime'] = strtotime($date[1].' 23:59:59');
        }

        if (empty($conditions['orgIds'])) {
            $userIds = -1;
        } else {
            $conditions['orgIds'] = explode(',', $conditions['orgIds']);
            $userIds = $this->findUserIdsByOrgIds($conditions['orgIds']);
            $userIds = empty($userIds) ? -1 : $userIds;
        }

        $keywordUserIds = array();
        if (isset($conditions['keywordType']) && !empty($conditions['keyword'])) {
            $users = $this->getUserService()->searchUsers(
                array(
                    $conditions['keywordType'] => $conditions['keyword'],
                ),
                array('id' => 'DESC'),
                0,
                PHP_INT_MAX
            );

            $keywordUserIds = empty($users) ? -1 : ArrayToolkit::column($users, 'id');
            unset($conditions['keywordType']);
            unset($conditions['keyword']);
        }

        if (-1 == $keywordUserIds || -1 == $userIds) {
            $conditions['userIds'] = array(-1);
        } elseif (empty($keywordUserIds)) {
            $conditions['userIds'] = $userIds;
        } else {
            $conditions['userIds'] = array_intersect($userIds, $keywordUserIds);
        }

        unset($conditions['orgIds']);

        return $conditions;
    }

    protected function findUserIdsByOrgIds($orgIds)
    {
        $usersOrg = $this->getUserOrgService()->findUserOrgsByOrgIds($orgIds);

        return ArrayToolkit::column($usersOrg, 'userId');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\UserDailyLearnRecord\Service\UserDailyLearnRecordService
     */
    protected function getUserDailyLearnRecordService()
    {
        return $this->createService('CorporateTrainingBundle:UserDailyLearnRecord:UserDailyLearnRecordService');
    }

    /**
     * @return DataStatisticsService
     */
    protected function getDataStatisticsService()
    {
        return $this->createService('CorporateTrainingBundle:DataStatistics:DataStatisticsService');
    }
}
