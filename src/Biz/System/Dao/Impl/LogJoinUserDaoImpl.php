<?php

namespace Biz\System\Dao\Impl;

use Biz\System\Dao\LogJoinUserDao;
use CorporateTrainingBundle\Common\BizFramework\Dao\JoinQueryGeneralDaoImpl;

class LogJoinUserDaoImpl extends JoinQueryGeneralDaoImpl implements LogJoinUserDao
{
    public function getJoinTables()
    {
        return array(
            'log' => 'log',
            'user' => 'user',
        );
    }

    public function declares()
    {
        return array(
            'conditions' => array(
                'log.module = :module',
                'log.action = :action',
                'log.level = :level',
                'log.userId = :userId',
                'log.createdTime > :startDateTime',
                'log.createdTime < :endDateTime',
                'UPPER(u.nickname) LIKE :nickname',
                'UPPER(u.email) LIKE :email',
                'u.orgCodes LIKE :likeOrgCode',
            ),
        );
    }

    public function searchLogJoinUser($conditions, $start, $limit)
    {
        $builder = $this->getLogJoinUserBuilder($conditions)
            ->select('log.*')
            ->orderBy('id', 'DESC')
            ->setFirstResult($start)
            ->setMaxResults($limit);

        return $builder->execute()->fetchAll();
    }

    public function countLogJoinUser($conditions)
    {
        $builder = $this->getLogJoinUserBuilder($conditions)->select('COUNT(*)');

        return (int) $builder->execute()->fetchColumn(0);
    }

    protected function getLogJoinUserBuilder($conditions)
    {
        $tables = $this->getJoinTables();

        $builder = $this->createQueryBuilder($conditions)
            ->from($tables['log'], 'log')
            ->innerJoin('log', $tables['user'], 'u', 'log.userId=u.id');

        return $builder;
    }
}
