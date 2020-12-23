<?php

namespace CorporateTrainingBundle\Biz\ProjectPlan\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ProjectPlanDao extends GeneralDaoInterface
{
    public function findByIds($ids);

    public function findByCreatedUserId($createdUserId);

    public function findByCategoryId($categoryId);

    public function findMonthlyProjectPlanIdsByOrgAndCategory($date, $orgCode, $categoryId);

    public function findByDateAndIds($date, $ids, $start, $limit);

    public function countByDateAndIds($date, $ids);
}
