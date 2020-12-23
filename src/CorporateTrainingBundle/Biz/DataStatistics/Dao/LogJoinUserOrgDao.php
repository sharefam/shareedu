<?php

namespace CorporateTrainingBundle\Biz\DataStatistics\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface LogJoinUserOrgDao extends GeneralDaoInterface
{
    /*统计某天的登入人数分布 按小时分布统计*/
    public function statisticsHourLoginByDate(array $conditions, $date);
}
