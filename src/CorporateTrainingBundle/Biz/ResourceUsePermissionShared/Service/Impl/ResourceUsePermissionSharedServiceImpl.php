<?php

namespace CorporateTrainingBundle\Biz\ResourceUsePermissionShared\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use CorporateTrainingBundle\Biz\ResourceUsePermissionShared\Dao\ResourceUsePermissionSharedDao;
use CorporateTrainingBundle\Biz\ResourceUsePermissionShared\Service\ResourceUsePermissionSharedHistoryService;
use CorporateTrainingBundle\Biz\ResourceUsePermissionShared\Service\ResourceUsePermissionSharedService;
use Biz\BaseService;

class ResourceUsePermissionSharedServiceImpl extends BaseService implements ResourceUsePermissionSharedService
{
    public function createSharedRecord($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('resourceType', 'resourceId', 'toUserId'))) {
            throw $this->createServiceException('parameter is invalid!');
        }
        $fields['fromUserId'] = $this->getCurrentUser()->getId();
        $fields = $this->filterFields($fields);

        $record = $this->getResourceUsePermissionSharedDao()->create($fields);
        $record['action'] = 'shared';
        $this->getResourceUsePermissionSharedHistoryService()->createHistory($record);

        return $record;
    }

    public function batchCreateSharedRecord($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('resourceType', 'resourceId', 'userIds'))) {
            throw $this->createServiceException('parameter is invalid!');
        }
        $records = array();
        $record = array(
            'resourceType' => $fields['resourceType'],
            'resourceId' => $fields['resourceId'],
            'fromUserId' => $this->getCurrentUser()->getId(),
        );

        foreach ($fields['userIds'] as $userId) {
            $sharedRecord = $this->getSharedRecordByResourceIdAndResourceTypeAndToUserId($fields['resourceId'], $fields['resourceType'], $userId);
            if (empty($sharedRecord)) {
                $record['toUserId'] = $userId;
                $records[] = $record;
            }
        }
        if (empty($records)) {
            return true;
        }
        try {
            $this->beginTransaction();
            $this->getResourceUsePermissionSharedDao()->batchCreate($records);
            $fields['userIds'] = ArrayToolkit::column($records, 'toUserId');
            $fields['action'] = 'shared';
            $this->getResourceUsePermissionSharedHistoryService()->batchCreateHistory($fields);
            $this->commit();
        } catch (\Exception $e) {
            $this->getLogger()->error('batchCreateSharedRecord:'.$e->getMessage());
            $this->rollback();
            throw $e;
        }

        return true;
    }

    public function updateSharedRecord($id, $fields)
    {
        return $this->getResourceUsePermissionSharedDao()->update($id, $fields);
    }

    public function deleteSharedRecord($id)
    {
        return $this->getResourceUsePermissionSharedDao()->delete($id);
    }

    public function getSharedRecord($id)
    {
        return $this->getResourceUsePermissionSharedDao()->get($id);
    }

    public function canceledSharedRecord($id)
    {
        $record = $this->getSharedRecord($id);
        if (empty($record)) {
            return true;
        }
        try {
            $this->beginTransaction();
            $result = $this->getResourceUsePermissionSharedDao()->delete($id);
            $record['action'] = 'canceled';
            $this->getResourceUsePermissionSharedHistoryService()->createHistory($record);

            $this->commit();
        } catch (\Exception $e) {
            $this->getLogger()->error('canceledSharedRecord:'.$e->getMessage());
            $this->rollback();
            throw $e;
        }

        return $result;
    }

    public function searchSharedRecords($conditions, $orderBys, $start, $limit, $columns = array())
    {
        return $this->getResourceUsePermissionSharedDao()->search($conditions, $orderBys, $start, $limit, $columns);
    }

    public function countSharedRecords($conditions)
    {
        return $this->getResourceUsePermissionSharedDao()->count($conditions);
    }

    public function findSharedRecordsByToUserId($userId)
    {
        return $this->getResourceUsePermissionSharedDao()->findByToUserId($userId);
    }

    public function getSharedRecordByResourceIdAndResourceTypeAndToUserId($resourceId, $resourceType, $toUserId)
    {
        return $this->getResourceUsePermissionSharedDao()->getByResourceIdAndResourceTypeAndToUserId($resourceId, $resourceType, $toUserId);
    }

    public function findSharedRecordsByFromUserId($userId)
    {
        return $this->getResourceUsePermissionSharedDao()->findByFromUserId($userId);
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
            )
        );
    }

    /**
     * @return ResourceUsePermissionSharedDao
     */
    protected function getResourceUsePermissionSharedDao()
    {
        return $this->createDao('CorporateTrainingBundle:ResourceUsePermissionShared:ResourceUsePermissionSharedDao');
    }

    /**
     * @return ResourceUsePermissionSharedHistoryService
     */
    protected function getResourceUsePermissionSharedHistoryService()
    {
        return $this->createService('ResourceUsePermissionShared:ResourceUsePermissionSharedHistoryService');
    }
}
