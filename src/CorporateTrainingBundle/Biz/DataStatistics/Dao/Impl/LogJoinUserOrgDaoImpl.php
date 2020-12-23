<?php

namespace CorporateTrainingBundle\Biz\DataStatistics\Dao\Impl;

use CorporateTrainingBundle\Biz\DataStatistics\Dao\Base\AbstractDataStatisticsDaoImpl;
use CorporateTrainingBundle\Biz\DataStatistics\Dao\LogJoinUserOrgDao;

class LogJoinUserOrgDaoImpl extends AbstractDataStatisticsDaoImpl implements LogJoinUserOrgDao
{
    public function statisticsHourLoginByDate(array $conditions, $date)
    {
        $builder = $this->db()->createQueryBuilder()
            ->select('HOUR(FROM_UNIXTIME(log.createdTime)) AS hour, count(log.userId) as count')
            ->from('log', 'log')
            ->where('FROM_UNIXTIME(log.createdTime, \'%Y-%m-%d\') = :date')
            ->setParameter('date', $date)
            ->groupBy('HOUR(FROM_UNIXTIME(log.createdTime))')
            ->orderBy('hour');

        if (!empty($conditions['postId']) || !empty($conditions['likeOrgCode'])) {
            $builder->innerJoin('log', 'user', 'u', 'log.userId=u.id');
        }

        $builder = $this->addWhereToQueryBuilder($builder, $conditions);

        return $builder->execute()->fetchAll() ?: array();
    }

    protected function getAllowedWhereConditions()
    {
        return array(
            'module' => 'log.module = :module',
            'action' => 'log.action = :action',
            'postId' => 'u.postId = :postId',
            'likeOrgCode' => 'u.orgCodes LIKE :likeOrgCode',
        );
    }
}
