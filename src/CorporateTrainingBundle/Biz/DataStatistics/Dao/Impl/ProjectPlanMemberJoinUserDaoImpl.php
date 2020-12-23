<?php

namespace CorporateTrainingBundle\Biz\DataStatistics\Dao\Impl;

use CorporateTrainingBundle\Biz\DataStatistics\Dao\Base\AbstractDataStatisticsDaoImpl;
use CorporateTrainingBundle\Biz\DataStatistics\Dao\ProjectPlanMemberJoinUserDao;

class ProjectPlanMemberJoinUserDaoImpl extends AbstractDataStatisticsDaoImpl implements ProjectPlanMemberJoinUserDao
{
    public function statisticsProjectPlanJoinNum(array $conditions)
    {
        $builder = $this->db()->createQueryBuilder()
            ->select('COUNT(ppm.id) as totalProjectNum')
            ->from('user', 'u')
            ->innerJoin('u', 'project_plan_member', 'ppm', 'u.id=ppm.userId')
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

    public function statisticsHotProjectPlanCategoryIdsAndJoinNum(array $conditions, $limit)
    {
        $builder = $this->db()->createQueryBuilder()
            ->select('category.id as categoryId, COUNT(ppm.id) as totalJoinNum')
            ->from('project_plan_member', 'ppm')
            ->innerJoin('ppm', 'project_plan', 'pp', 'pp.id=ppm.projectPlanId')
            ->innerJoin('pp', 'category', 'category', 'pp.categoryId = category.id')
            ->innerJoin('ppm', 'user', 'u', 'u.id=ppm.userId')
            ->where('u.locked = 0')
            ->andWhere('u.type != "system"')
            ->andWhere('pp.categoryId != 0')
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
            'startDateTime' => 'ppm.createdTime >= :startDateTime',
            'endDateTime' => 'ppm.createdTime <= :endDateTime',
            'orgIds' => 'u.orgId in (:orgIds)',
            'likeOrgCode' => 'uo.orgCode PRE_LIKE :likeOrgCode',
        );
    }
}
