<?php

namespace CorporateTrainingBundle\Biz\ProjectPlan\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface MemberDao extends GeneralDaoInterface
{
    public function getByUserIdAndProjectPlanId($userId, $projectPlanId);

    public function findByIds($ids);

    public function findByUserId($userId);

    public function findByProjectPlanId($projectPlanId);

    public function deleteMemberByProjectPlanId($projectPlanId);

    public function calculateProjectPlanLearnDataByUserIdsAndDate($userIds, $date);
}
