<?php

namespace  CorporateTrainingBundle\Biz\User\Dao\Impl;

use Biz\User\Dao\Impl\UserProfileDaoImpl as BaseDaoImpl;

class UserProfileDaoImpl extends BaseDaoImpl
{
    public function findUserIds(array $conditions)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select('DISTINCT id');

        return $builder->execute()->fetchAll() ?: array();
    }

    public function findByTrueName($truename)
    {
        return $this->findByFields(array('truename' => $truename));
    }
}
