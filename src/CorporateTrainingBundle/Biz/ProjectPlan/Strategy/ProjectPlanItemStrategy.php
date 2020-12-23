<?php

namespace CorporateTrainingBundle\Biz\ProjectPlan\Strategy;

interface ProjectPlanItemStrategy
{
    public function createItems($projectPlanId, $items, $itemType = null);

    public function updateItem($id, $item, $itemType = null);

    public function deleteItem($item);

    public function getItem($item);

    public function getTaskReviewNum($taskId);

    public function getStudyResult($item, $user);

    public function getItemInfoByUserId($projectPlanItem, $userId);

    public function findItemsDetail($items);

    public function findTasksByItemIdAndTimeRange($itemId, $timeRange);

    public function isFinished($item, $user);

    public function findFinishedMembers($item);

    public function getFinishedMembersNum($item);
}
