<?php

namespace CorporateTrainingBundle\Biz\UserGroup\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface UserGroupDao extends GeneralDaoInterface
{
    public function getByName($name);

    public function getByCode($code);

    public function findByIds($ids);
}
