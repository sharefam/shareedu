<?php

namespace CorporateTrainingBundle\Biz\ResourceUsePermissionShared\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use CorporateTrainingBundle\Biz\ResourceUsePermissionShared\Dao\ResourceUsePermissionSharedHistoryDao;
use CorporateTrainingBundle\Biz\ResourceUsePermissionShared\Service\ResourceUsePermissionSharedHistoryService;
use Biz\BaseService;
use CorporateTrainingBundle\Biz\ResourceUsePermissionShared\Service\ResourceUsePermissionSharedService;

class ResourceUsePermissionSharedHistoryServiceImpl extends BaseService implements ResourceUsePermissionSharedHistoryService
{
    public function createHistory($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('resourceType', 'resourceId', 'toUserId', 'fromUserId', 'action'))) {
            throw $this->createServiceException('parameter is invalid!');
        }

        $fields['actionUser'] = $this->getCurrentUser()->getId();
        $fields = $this->filterFields($fields);

        return $this->getResourceUsePermissionSharedHistoryDao()->create($fields);
    }

    public function batchCreateHistory($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('resourceType', 'resourceId', 'userIds', 'action'))) {
            throw $this->createServiceException('parameter is invalid!');
        }

        $sharedRecords = $this->getResourceUsePermissionSharedService()->searchSharedRecords(array('toUserIds' => $fields['userIds'], 'resourceType' => $fields['resourceType'], 'resourceId' => $fields['resourceId']), array(), 0, PHP_INT_MAX);
        if (empty($sharedRecords)) {
            return true;
        }
        $records = array();
        $record = array(
            'resourceType' => $fields['resourceType'],
            'resourceId' => $fields['resourceId'],
            'actionUser' => $this->getCurrentUser()->getId(),
            'action' => $fields['action'],
        );
        foreach ($sharedRecords as $sharedRecord) {
            $record['toUserId'] = $sharedRecord['toUserId'];
            $record['fromUserId'] = $sharedRecord['fromUserId'];
            $records[] = $record;
        }
        $this->getResourceUsePermissionSharedHistoryDao()->batchCreate($records);

        return true;
    }

    public function updateHistory($id, $fields)
    {
        return $this->getResourceUsePermissionSharedHistoryDao()->update($id, $fields);
    }

    public function deleteHistory($id)
    {
        return $this->getResourceUsePermissionSharedHistoryDao()->delete($id);
    }

    public function searchHistories($conditions, $orderBys, $start, $limit, $columns = array())
    {
        return $this->getResourceUsePermissionSharedHistoryDao()->search($conditions, $orderBys, $start, $limit, $columns);
    }

    public function findHistoriesByToUserId($userId)
    {
        return $this->getResourceUsePermissionSharedHistoryDao()->findByToUserId($userId);
    }

    public function findHistoriesByFromUserId($userId)
    {
        return $this->getResourceUsePermissionSharedHistoryDao()->findByFromUserId($userId);
    }

    protected function filterFields($fields)
    {
        return ArrayToolkit::parts(
            $fields,
            array(
                'resourceType',
                'resourceId',
                'toUserId',
                'fromUserId',
                'action',
                'actionUser',
            )
        );
    }

    /**
     * @return ResourceUsePermissionSharedHistoryDao
     */
    protected function getResourceUsePermissionSharedHistoryDao()
    {
        return $this->createDao('CorporateTrainingBundle:ResourceUsePermissionShared:ResourceUsePermissionSharedHistoryDao');
    }

    /**
     * @return ResourceUsePermissionSharedService
     */
    protected function getResourceUsePermissionSharedService()
    {
        return $this->createService('ResourceUsePermissionShared:ResourceUsePermissionSharedService');
    }
}
