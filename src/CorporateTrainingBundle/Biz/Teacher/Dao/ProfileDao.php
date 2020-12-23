<?php

namespace CorporateTrainingBundle\Biz\Teacher\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ProfileDao extends GeneralDaoInterface
{
    public function getByUserId($userId);

    public function findByIds($ids);

    public function findByLevelId($levelId);
}
