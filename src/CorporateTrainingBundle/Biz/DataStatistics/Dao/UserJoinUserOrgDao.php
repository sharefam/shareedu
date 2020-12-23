<?php

namespace CorporateTrainingBundle\Biz\DataStatistics\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface UserJoinUserOrgDao extends GeneralDaoInterface
{
    /*统计学员总数*/
    public function statisticsTotalUserNum(array $conditions);
}
