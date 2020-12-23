<?php

namespace CorporateTrainingBundle\Biz\DataStatistics\Dao\Impl;

use CorporateTrainingBundle\Biz\DataStatistics\Dao\Base\AbstractDataStatisticsDaoImpl;
use CorporateTrainingBundle\Biz\DataStatistics\Dao\UserDailyLearnRecordJoinUserDao;

class UserDailyLearnRecordJoinUserDaoImpl extends AbstractDataStatisticsDaoImpl implements UserDailyLearnRecordJoinUserDao
{
    public function statisticsPersonLearnTimeRankingList(array $conditions, $start = 0, $limit = 5)
    {
        $builder = $this->db()->createQueryBuilder()
            ->select('udlr.userId as userId, MAX(udlr.postId) as postId, SUM(udlr.learnTime)/COUNT(DISTINCT(uo.orgId)) as totalLearnTime')
            ->from('user_daily_learn_record', 'udlr')
            ->innerJoin('udlr', 'user', 'u', 'u.id=udlr.userId')
            ->innerJoin('udlr', 'user_org', 'uo', 'udlr.userId=uo.userId')
            ->where('u.locked = 0')
            ->andWhere('u.type != "system"')
            ->groupBy('userId')
            ->orderBy('totalLearnTime', 'DESC')
            ->setFirstResult($start)
            ->setMaxResults($limit);

        $builder = $this->addWhereToQueryBuilder($builder, $conditions);

        return $builder->execute()->fetchAll() ?: array();
    }

    public function statisticsOnlineCourseLearnTime(array $conditions)
    {
        $builder = $this->db()->createQueryBuilder()
            ->select('COALESCE(SUM(udlr.learnTime),0) as totalLearnTime')
            ->from('user', 'u')
            ->innerJoin('u', 'user_daily_learn_record', 'udlr', 'u.id=udlr.userId')
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

    public function statisticsOnlineCourseLearnNum(array $conditions)
    {
        $builder = $this->db()->createQueryBuilder()
            ->select('COUNT(DISTINCT udlr.courseId, udlr.userId) as totalCourseNum')
            ->from('user', 'u')
            ->innerJoin('u', 'user_daily_learn_record', 'udlr', 'u.id=udlr.userId')
            ->where('u.locked = 0')
            ->andWhere('u.type != "system"');

        if (isset($conditions['likeOrgCode'])) {
            $builder->innerJoin('u', 'user_org', 'uo', 'u.id=uo.userId');
        }

        $builder = $this->addWhereToQueryBuilder($builder, $conditions);

        return $builder->execute()->fetchColumn() ?: 0;
    }

    public function statisticsHotOnlineCourseCategoryIdsAndJoinNum($conditions, $limit)
    {
        $builder = $this->db()->createQueryBuilder()
            ->select('category.id as categoryId, COUNT(DISTINCT udlr.courseId, udlr.userId) as totalJoinNum')
            ->from('user_daily_learn_record', 'udlr')
            ->innerJoin('udlr', 'course_v8', 'course', 'course.id = udlr.courseId')
            ->innerJoin('course', 'category', 'category', 'course.categoryId = category.id')
            ->innerJoin('udlr', 'user', 'u', 'u.id=udlr.userId')
            ->where('u.locked = 0')
            ->andWhere('u.type != "system"')
            ->andWhere('course.categoryId != 0')
            ->groupBy('category.id')
            ->orderBy('totalJoinNum', 'DESC')
            ->setMaxResults($limit);

        if (isset($conditions['likeOrgCode'])) {
            $builder->innerJoin('u', 'user_org', 'uo', 'u.id=uo.userId');
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
            'startDateTime' => 'udlr.date >= :startDateTime',
            'endDateTime' => 'udlr.date <= :endDateTime',
            'orgIds' => 'u.orgId in (:orgIds)',
            'likeOrgCode' => 'uo.orgCode PRE_LIKE :likeOrgCode',
        );
    }
}
