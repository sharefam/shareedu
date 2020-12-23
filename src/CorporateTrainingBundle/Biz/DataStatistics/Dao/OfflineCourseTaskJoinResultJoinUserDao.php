<?php

namespace CorporateTrainingBundle\Biz\DataStatistics\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface OfflineCourseTaskJoinResultJoinUserDao extends GeneralDaoInterface
{
    /*统计个人线下学习时长排行*/
    public function statisticsPersonLearnTimeRankingList(array $conditions, $start, $limit);

    /*统计线下学习总时长*/
    public function statisticsOfflineCourseLearnTime(array $conditions);
}
