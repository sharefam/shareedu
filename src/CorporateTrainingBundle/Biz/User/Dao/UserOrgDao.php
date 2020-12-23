<?php

namespace CorporateTrainingBundle\Biz\User\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface UserOrgDao extends GeneralDaoInterface
{
    public function findByUserId($userId);

    public function findByOrgIds(array $orgIds);
}
