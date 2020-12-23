<?php

namespace CorporateTrainingBundle\Biz\DataStatistics\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ProjectPlanMemberJoinUserDao extends GeneralDaoInterface
{
    /*统计培训项目加入人数*/
    public function statisticsProjectPlanJoinNum(array $conditions);

    /*统计热门培训项目分类和加入人数*/
    public function statisticsHotProjectPlanCategoryIdsAndJoinNum(array $conditions, $limit);
}
