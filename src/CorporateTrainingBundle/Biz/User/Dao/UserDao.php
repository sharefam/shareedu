<?php

namespace CorporateTrainingBundle\Biz\User\Dao;

use Biz\User\Dao\UserDao as BaseUserDao;

interface UserDao extends BaseUserDao
{
    public function findUserIds(array $conditions);
}
