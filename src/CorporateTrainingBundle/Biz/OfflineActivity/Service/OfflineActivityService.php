<?php

namespace CorporateTrainingBundle\Biz\OfflineActivity\Service;

interface OfflineActivityService
{
    public function searchOfflineActivities($conditions, $orderBy, $start, $limit);

    public function countOfflineActivities($conditions);

    public function createOfflineActivity($offlineActivity);

    public function hasActivityManageRole();

    public function changeActivityCover($id, $coverArray);

    public function updateOfflineActivity($id, $fields);

    public function getOfflineActivity($id);

    public function applyAttendOfflineActivity($activityId, $userId);

    public function publishOfflineActivity($activityId);

    public function closeOfflineActivity($activityId);

    public function getUserApplyStatus($activityId, $userId);

    public function refreshMemberCount($activityId);

    public function findOfflineActivitiesByIds($ids);

    public function findOfflineActivitiesByCategoryId($categoryId);

    public function canManageOfflineActivity($activityId);
}
