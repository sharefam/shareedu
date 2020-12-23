<?php

namespace CorporateTrainingBundle\Biz\DataStatistics\Service;

interface DataStatisticsService
{
    public function statisticsLearnRecordGroupByOrgId(array $conditions);

    public function statisticsLearnRecordGroupByPostId(array $conditions);

    public function statisticsLearnRecordGroupByCategoryId(array $conditions);

    public function statisticsOrgLearnTimeRankingList(array $conditions, $start = 0, $limit = 5);

    public function statisticsPersonOnlineLearnTimeRankingList(array $conditions, $start = 0, $limit = 5);

    public function statisticsPersonOfflineLearnTimeRankingList(array $conditions, $start = 0, $limit = 5);

    public function statisticsLearnUsersNumGroupByDate(array $conditions);

    public function statisticsTotalLearnTimeGroupByDate(array $conditions);

    public function statisticsWebDailyLogin(array $conditions, $date);

    public function statisticsAPPDailyLogin(array $conditions, $date);

    public function statisticsUserNum(array $conditions);

    public function statisticsOnlineCourseLearnNum(array $conditions);

    public function statisticsOnlineCourseLearnTime(array $conditions);

    public function statisticsProjectPlanJoinNum(array $conditions);

    public function statisticsOfflineCourseLearnTime(array $conditions);

    public function statisticsHotOnlineCourseCategoryIdsAndJoinNum(array $conditions, $limit);

    public function statisticsHotProjectPlanCategoryIdsAndJoinNum(array $conditions, $limit);

    public function statisticsHotOfflineActivityCategoryIdsAndJoinNum(array $conditions, $limit);

    public function getOnlineCourseLearnDataForUserLearnDataExtension(array $conditions);

    public function getOnlineStudyHoursLearnDataForUserLearnDataExtension(array $conditions);

    public function getPostCourseLearnDataForUserLearnDataExtension(array $conditions);

    public function getPostCourseLearnProgressForUserLearnDataExtension(array $conditions);

    public function getPostCourseLearnHourForUserLearnDataExtension(array $conditions);
}
