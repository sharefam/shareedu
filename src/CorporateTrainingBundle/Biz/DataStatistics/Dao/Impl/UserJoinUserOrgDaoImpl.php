<?php

namespace CorporateTrainingBundle\Biz\DataStatistics\Dao\Impl;

use CorporateTrainingBundle\Biz\DataStatistics\Dao\Base\AbstractDataStatisticsDaoImpl;
use CorporateTrainingBundle\Biz\DataStatistics\Dao\UserJoinUserOrgDao;

class UserJoinUserOrgDaoImpl extends AbstractDataStatisticsDaoImpl implements UserJoinUserOrgDao
{
    public function statisticsTotalUserNum(array $conditions)
    {
        $builder = $this->db()->createQueryBuilder()
            ->select('COUNT(DISTINCT u.id) as totalUserNum')
            ->from('user', 'u')
            ->innerJoin('u', 'user_org', 'uo', 'u.id=uo.userId')
            ->where('u.locked = 0')
            ->andWhere('u.type != "system"');

        $builder = $this->addWhereToQueryBuilder($builder, $conditions);

        return $builder->execute()->fetchColumn() ?: 0;
    }

    protected function getAllowedWhereConditions()
    {
        return array(
            'email' => 'u.email = :email',
            'postId' => 'u.postId = :postId',
            'hireDate_GTE' => 'u.hireDate > :hireDate_GTE',
            'hireDate_LTE' => 'u.hireDate < :hireDate_LTE',
            'likeOrgCode' => 'uo.orgCode PRE_LIKE :likeOrgCode',
        );
    }
}
