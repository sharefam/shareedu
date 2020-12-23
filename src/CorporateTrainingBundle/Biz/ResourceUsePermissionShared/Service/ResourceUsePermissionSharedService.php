<?php

namespace CorporateTrainingBundle\Biz\ResourceUsePermissionShared\Service;

interface ResourceUsePermissionSharedService
{
    public function createSharedRecord($fields);

    public function batchCreateSharedRecord($fields);

    public function updateSharedRecord($id, $fields);

    public function deleteSharedRecord($id);

    public function countSharedRecords($conditions);

    public function getSharedRecord($id);

    public function searchSharedRecords($conditions, $orderBys, $start, $limit, $columns = array());

    public function findSharedRecordsByToUserId($userId);

    public function findSharedRecordsByFromUserId($userId);

    public function getSharedRecordByResourceIdAndResourceTypeAndToUserId($resourceId, $resourceType, $toUserId);

    public function canceledSharedRecord($id);
}
