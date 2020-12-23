<?php

namespace CorporateTrainingBundle\Biz\ProjectPlan\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ProjectPlanItemDao extends GeneralDaoInterface
{
    public function getByTargetIdAndTargetType($targetId, $targetType);

    public function getByProjectPlanIdAndTargetIdAndTargetType($projectPlanId, $targetId, $targetType);

    public function findByIds($ids);

    public function findByProjectPlanId($projectPlanId);

    public function findByProjectPlanIds($projectPlanIds);

    public function deleteItemByProjectPlanId($projectPlanId);

    public function findByProjectPlanIdAndTargetType($projectPlanId, $targetType);

    public function findByTargetIdAndTargetType($targetId, $targetType);

    public function findHasFinishedSurveyResultProjectPlanItemIds($projectPlanId, $userIds);
}
