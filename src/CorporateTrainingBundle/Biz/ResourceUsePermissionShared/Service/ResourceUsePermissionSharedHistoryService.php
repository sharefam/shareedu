<?php

namespace CorporateTrainingBundle\Biz\ResourceUsePermissionShared\Service;

interface ResourceUsePermissionSharedHistoryService
{
    public function createHistory($fields);

    public function batchCreateHistory($fields);

    public function updateHistory($id, $fields);

    public function deleteHistory($id);

    public function searchHistories($conditions, $orderBys, $start, $limit, $columns = array());

    public function findHistoriesByToUserId($userId);

    public function findHistoriesByFromUserId($userId);
}
