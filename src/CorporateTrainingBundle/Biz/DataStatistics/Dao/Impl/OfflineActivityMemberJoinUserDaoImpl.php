<?php

namespace CorporateTrainingBundle\Biz\DataStatistics\Dao\Impl;

use CorporateTrainingBundle\Biz\DataStatistics\Dao\Base\AbstractDataStatisticsDaoImpl;
use CorporateTrainingBundle\Biz\DataStatistics\Dao\OfflineActivityMemberJoinUserDao;

class OfflineActivityMemberJoinUserDaoImpl extends AbstractDataStatisticsDaoImpl implements OfflineActivityMemberJoinUserDao
{
    public function statisticsOfflineActivityJoinNum(array $conditions)
    {
        $builder = $this->db()->createQueryBuilder()
            ->select('COUNT(oam.id) as totalActivityNum')
            ->from('user', 'u')
            ->innerJoin('u', 'offline_activity_member', 'oam', 'u.id=oam.userId')
            ->where('u.locked = 0')
            ->andWhere('u.type != "system"');

        if (isset($conditions['likeOrgCode'])) {
            $builder->innerJoin('u', '(SELECT DISTINCT userId from user_org WHERE orgCode LIKE :likeOrgCode)', 'temp', 'temp.userId = u.id');
            $builder->setParameter('likeOrgCode', $conditions['likeOrgCode'].'%');
            unset($conditions['likeOrgCode']);
        }

        $builder = $this->addWhereToQueryBuilder($builder, $conditions);

        return $builder->execute()->fetchColumn() ?: 0;
    }

    public function statisticsHotOfflineActivityCategoryIdsAndJoinNum($conditions, $limit)
    {
        $builder = $this->db()->createQueryBuilder()
            ->select('category.id as categoryId, COUNT(oam.id) as totalJoinNum')
            ->from('offline_activity_member', 'oam')
            ->innerJoin('oam', 'offline_activity', 'oa', 'oa.id=oam.offlineActivityId')
            ->innerJoin('oa', 'category', 'category', 'oa.categoryId = category.id')
            ->innerJoin('oam', 'user', 'u', 'u.id=oam.userId')
            ->where('u.locked = 0')
            ->andWhere('u.type != "system"')
            ->andWhere('oa.categoryId != 0')
            ->groupBy('category.id')
            ->orderBy('totalJoinNum', 'DESC')
            ->setMaxResults($limit);

        if (isset($conditions['likeOrgCode'])) {
            $builder->innerJoin('u', '(SELECT DISTINCT userId from user_org WHERE orgCode LIKE :likeOrgCode)', 'temp', 'temp.userId = u.id');
            $builder->setParameter('likeOrgCode', $conditions['likeOrgCode'].'%');
            unset($conditions['likeOrgCode']);
        }

        $builder = $this->addWhereToQueryBuilder($builder, $conditions);

        return $builder->execute()->fetchAll() ?: array();
    }

    protected function getAllowedWhereConditions()
    {
        return array(
            'email' => 'u.email = :email',
            'postId' => 'u.postId = :postId',
            'hireDate_GTE' => 'u.hireDate > :hireDate_GTE',
            'hireDate_LTE' => 'u.hireDate < :hireDate_LTE',
            'startDateTime' => 'oam.createdTime >= :startDateTime',
            'endDateTime' => 'oam.createdTime <= :endDateTime',
            'orgIds' => 'u.orgId in (:orgIds)',
            'likeOrgCode' => 'uo.orgCode PRE_LIKE :likeOrgCode',
        );
    }
}
