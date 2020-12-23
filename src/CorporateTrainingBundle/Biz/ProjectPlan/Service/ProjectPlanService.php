<?php

namespace CorporateTrainingBundle\Biz\ProjectPlan\Service;

interface ProjectPlanService
{
    /**
     *  ProjectPlan API.
     */
    public function createProjectPlan($projectPlan);

    public function updateProjectPlan($id, $fields);

    public function deleteProjectPlan($id);

    public function getProjectPlan($id);

    public function getMonthlyProjectPlansNumAndMembersNumByOrgAndCategory($date, $orgId, $categoryId);

    public function findProjectPlansByIds($ids);

    public function findProjectPlansByCreatedUserId($createdUserId);

    public function findProjectPlanByCategoryId($categoryId);

    public function findUnfinishedProjectPlansByCurrentUserId($start, $limit);

    public function findProjectPlansByDateAndIds($date, $projectIds, $start, $limit);

    public function countProjectPlansByDateAndIds($date, $projectIds);

    public function countProjectPlans(array $conditions);

    public function countUnfinishedProjectPlansByCurrentUserId();

    public function searchProjectPlans(array $conditions, array $orderBys, $start, $limit);

    public function publishProjectPlan($id);

    public function closeProjectPlan($id);

    /**
     * 项目归档.
     */
    public function archiveProjectPlan($id);

    public function canManageProjectPlan($id);

    public function changeCover($id, $coverArray);

    public function applyAttendProjectPlan($recordId, $fields);

    public function getUserApplyStatus($projectPlanId, $userId);

    /**
     *  ProjectPlanItem API.
     */
    public function createProjectPlanItem($item);

    public function updateProjectPlanItem($id, $fields);

    public function updateProjectPlanItemTime($id, $fields);

    public function updatePlanItem($id, $fields, $type);

    public function deleteProjectPlanItem($id);

    public function deleteItemByProjectPlanId($projectPlanId);

    public function getProjectPlanItem($id);

    public function getProjectPlanItemByTargetIdAndTargetType($targetId, $targetType);

    public function getProjectPlanItemByProjectPlanIdAndTargetIdAndTargetType($projectPlanId, $targetId, $targetType);

    public function findProjectPlanItemsByIds($ids);

    public function findProjectPlanItemsByProjectPlanId($projectPlanId);

    public function findProjectPlanItemsByProjectPlanIds(array $projectPlanIds);

    public function findHasFinishedSurveyResultProjectPlanItemIds($id, $userIds);

    /**
     * 查询培训项目Item.
     *
     * @param ($target, $targetType)
     * 同一一个课程可以对应多个培训项目，所以通过targetId和targetType查询的item不唯一
     */
    public function findProjectPlanItemsByTargetIdAndTargetType($targetId, $targetType);

    public function countProjectPlanItems(array $conditions);

    public function searchProjectPlanItems(array $conditions, array $orderBys, $start, $limit);

    public function setProjectPlanItems($projectPlanId, $items, $type);

    public function findProjectPlanItemsByProjectPlanIdAndTargetType($projectPlanId, $targetType);

    public function sortItems($ids);

    /**
     *  ProjectPlanAdvance API.
     */
    public function createProjectPlanAdvancedOption($fields);

    public function updateProjectPlanAdvancedOption($id, $projectPlan);

    public function getProjectPlanAdvancedOptionByProjectPlanId($projectPlanId);

    public function countProjectPlanAdvancedOptions($conditions);

    public function searchProjectPlanAdvancedOptions(array $conditions, array $orderBys, $start, $limit);
}
