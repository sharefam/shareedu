<?php

namespace CorporateTrainingBundle\Biz\DataStatistics\Dao\Impl;

use CorporateTrainingBundle\Biz\DataStatistics\Dao\Base\AbstractDataStatisticsDaoImpl;
use CorporateTrainingBundle\Biz\DataStatistics\Dao\OfflineCourseTaskJoinResultJoinUserDao;

class OfflineCourseTaskJoinResultJoinUserDaoImpl extends AbstractDataStatisticsDaoImpl implements OfflineCourseTaskJoinResultJoinUserDao
{
    public function statisticsPersonLearnTimeRankingList(array $conditions, $start, $limit)
    {
        $builder = $this->db()->createQueryBuilder()
            ->select('result.userId AS userId, SUM(task.endTime - task.startTime)/COUNT(DISTINCT(uo.orgId)) AS totalLearnTime')
            ->from('offline_course_task_result', 'result')
            ->innerJoin('result', 'offline_course_task', 'task', 'task.id=result.taskId')
            ->innerJoin('result', 'user', 'u', 'result.userId=u.id')
            ->innerJoin('u', 'user_org', 'uo', 'u.id=uo.userId')
            ->where('u.locked = 0')
            ->andWhere('u.type != "system"')
            ->groupBy('userId')
            ->orderBy('totalLearnTime', 'DESC')
            ->setFirstResult($start)
            ->setMaxResults($limit);

        $builder = $this->addWhereToQueryBuilder($builder, $conditions);

        return $builder->execute()->fetchAll() ?: array();
    }

    public function statisticsOfflineCourseLearnTime(array $conditions)
    {
        $builder = $this->db()->createQueryBuilder()
            ->select('SUM(task.endTime - task.startTime) AS totalLearnTime')
            ->from('offline_course_task_result', 'result')
            ->innerJoin('result', 'offline_course_task', 'task', 'task.id=result.taskId')
            ->innerJoin('result', 'user', 'u', 'result.userId=u.id')
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

    protected function getAllowedWhereConditions()
    {
        return array(
            'email' => 'u.email = :email',
            'postId' => 'u.postId = :postId',
            'hireDate_GTE' => 'u.hireDate > :hireDate_GTE',
            'hireDate_LTE' => 'u.hireDate < :hireDate_LTE',
            'startDateTime' => 'task.endTime >= :startDateTime',
            'endDateTime' => 'task.endTime <= :endDateTime',
            'orgIds' => 'u.orgId in (:orgIds)',
            'likeOrgCode' => 'uo.orgCode PRE_LIKE :likeOrgCode',
        );
    }
}
