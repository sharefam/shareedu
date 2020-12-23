<?php

namespace CorporateTrainingBundle\Biz\OfflineActivity\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface OfflineActivityDao extends GeneralDaoInterface
{
    public function findOfflineActivitiesByIds($ids);

    public function findByCategoryId($categoryId);
}
