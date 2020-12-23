<?php

namespace CorporateTrainingBundle\Biz\User\Dao;

use Biz\User\Dao\UserProfileDao as BaseUserDao;

interface UserProfileDao extends BaseUserDao
{
    public function findUserIds(array $conditions);
}
