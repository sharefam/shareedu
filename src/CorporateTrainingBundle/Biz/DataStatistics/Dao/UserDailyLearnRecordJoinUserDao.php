<?php

namespace CorporateTrainingBundle\Biz\DataStatistics\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface UserDailyLearnRecordJoinUserDao extends GeneralDaoInterface
{
    /*统计个人显示学习时长排行*/
    public function statisticsPersonLearnTimeRankingList(array $conditions, $start = 0, $limit = 5);

    /*统计线上学习总时长*/
    public function statisticsOnlineCourseLearnTime(array $conditions);

    /*统计线上课程学习总数*/
    public function statisticsOnlineCourseLearnNum(array $conditions);

    /*统计热门线上课程分类和加入数*/
    public function statisticsHotOnlineCourseCategoryIdsAndJoinNum($conditions, $limit);
}
