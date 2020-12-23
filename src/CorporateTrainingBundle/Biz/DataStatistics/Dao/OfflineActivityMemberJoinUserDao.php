<?php

namespace CorporateTrainingBundle\Biz\DataStatistics\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface OfflineActivityMemberJoinUserDao extends GeneralDaoInterface
{
    /*统计线下活动参加人数*/
    public function statisticsOfflineActivityJoinNum(array $conditions);

    /*统计线下活动热门分类和加入人数*/
    public function statisticsHotOfflineActivityCategoryIdsAndJoinNum($conditions, $limit);
}
