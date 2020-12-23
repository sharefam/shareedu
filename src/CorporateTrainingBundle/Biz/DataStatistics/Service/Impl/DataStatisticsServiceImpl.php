<?php

namespace CorporateTrainingBundle\Biz\DataStatistics\Service\Impl;

use Biz\BaseService;
use AppBundle\Common\ArrayToolkit;
use CorporateTrainingBundle\Biz\DataStatistics\Dao\Base\DataStatisticsDaoRedisCacheProxy;
use CorporateTrainingBundle\Biz\DataStatistics\Service\DataStatisticsService;
use CorporateTrainingBundle\Biz\PostCourse\Service\PostCourseService;
use CorporateTrainingBundle\Biz\DataStatistics\Dao\UserDailyLearnRecordDao;
use CorporateTrainingBundle\Common\Constant\CTConst;

class DataStatisticsServiceImpl extends BaseService implements DataStatisticsService
{
    public function statisticsLearnRecordGroupByOrgId(array $conditions)
    {
        return $this->getUserDailyLearnRecordDao()->statisticsLearnRecordGroupByOrgId($conditions);
    }

    public function statisticsLearnRecordGroupByPostId(array $conditions)
    {
        return $this->getUserDailyLearnRecordDao()->statisticsLearnRecordGroupByPostId($conditions);
    }

    public function statisticsLearnRecordGroupByCategoryId(array $conditions)
    {
        return $this->getUserDailyLearnRecordDao()->statisticsLearnRecordGroupByCategoryId($conditions);
    }

    public function statisticsOrgLearnTimeRankingList(array $conditions, $start = 0, $limit = 5)
    {
        return $this->getUserDailyLearnRecordDao()->statisticsOrgLearnTimeRankingList($conditions, $start, $limit);
    }

    public function statisticsPersonOnlineLearnTimeRankingList(array $conditions, $start = 0, $limit = 5)
    {
        return $this->getUserDailyLearnRecordJoinUserDao()->statisticsPersonLearnTimeRankingList($conditions, $start,
            $limit);
    }

    public function statisticsPersonOfflineLearnTimeRankingList(array $conditions, $start = 0, $limit = 5)
    {
        return $this->getOfflineCourseTaskJoinResultJoinUserDao()->statisticsPersonLearnTimeRankingList($conditions,
            $start, $limit);
    }

    public function statisticsTotalLearnTimeGroupByDate(array $conditions)
    {
        return $this->getUserDailyLearnRecordDao()->statisticsTotalLearnTimeGroupByDate($conditions);
    }

    public function statisticsLearnUsersNumGroupByDate(array $conditions)
    {
        return $this->getUserDailyLearnRecordDao()->statisticsLearnUsersNumGroupByDate($conditions);
    }

    public function statisticsWebDailyLogin(array $conditions, $date)
    {
        if (isset($conditions['likeOrgCode']) && CTConst::ROOT_ORG_CODE == $conditions['likeOrgCode']) {
            unset($conditions['likeOrgCode']);
        } else {
            $conditions['likeOrgCode'] = '|'.$conditions['likeOrgCode'];
        }

        $conditions['action'] = 'login_success';
        $dailyLoginData = $this->getLogJoinUserOrgDao()->statisticsHourLoginByDate($conditions, $date);

        return ArrayToolkit::index($dailyLoginData, 'hour');
    }

    public function statisticsAPPDailyLogin(array $conditions, $date)
    {
        if (isset($conditions['likeOrgCode']) && CTConst::ROOT_ORG_CODE == $conditions['likeOrgCode']) {
            unset($conditions['likeOrgCode']);
        } else {
            $conditions['likeOrgCode'] = '|'.$conditions['likeOrgCode'];
        }

        $conditions['action'] = 'user_login';
        $conditions['module'] = 'mobile';
        $dailyLoginData = $this->getLogJoinUserOrgDao()->statisticsHourLoginByDate($conditions, $date);

        return ArrayToolkit::index($dailyLoginData, 'hour');
    }

    public function statisticsUserNum(array $conditions)
    {
        return $this->getUserJoinUserOrgDao()->statisticsTotalUserNum($conditions);
    }

    public function sumLearnTimeByUserId($userId)
    {
        return $this->getUserDailyLearnRecordDao()->sumLearnTimeByUserId($userId);
    }

    public function statisticsOnlineCourseLearnNum(array $conditions)
    {
        if (isset($conditions['likeOrgCode']) && CTConst::ROOT_ORG_CODE == $conditions['likeOrgCode']) {
            unset($conditions['likeOrgCode']);
        }

        return $this->getUserDailyLearnRecordJoinUserDao()->statisticsOnlineCourseLearnNum($conditions);
    }

    public function statisticsOnlineCourseLearnTime(array $conditions)
    {
        if (isset($conditions['likeOrgCode']) && CTConst::ROOT_ORG_CODE == $conditions['likeOrgCode']) {
            unset($conditions['likeOrgCode']);
        }

        return $this->getUserDailyLearnRecordJoinUserDao()->statisticsOnlineCourseLearnTime($conditions);
    }

    public function statisticsProjectPlanJoinNum(array $conditions)
    {
        if (isset($conditions['likeOrgCode']) && CTConst::ROOT_ORG_CODE == $conditions['likeOrgCode']) {
            unset($conditions['likeOrgCode']);
        }

        return $this->getProjectPlanMemberJoinUserDao()->statisticsProjectPlanJoinNum($conditions);
    }

    public function statisticsOfflineCourseLearnTime(array $conditions)
    {
        if (isset($conditions['likeOrgCode']) && CTConst::ROOT_ORG_CODE == $conditions['likeOrgCode']) {
            unset($conditions['likeOrgCode']);
        }

        return $this->getOfflineCourseTaskJoinResultJoinUserDao()->statisticsOfflineCourseLearnTime($conditions);
    }

    public function statisticsOfflineActivityJoinNum(array $conditions)
    {
        if (isset($conditions['likeOrgCode']) && CTConst::ROOT_ORG_CODE == $conditions['likeOrgCode']) {
            unset($conditions['likeOrgCode']);
        }

        return $this->getOfflineActivityMemberJoinUserDao()->statisticsOfflineActivityJoinNum($conditions);
    }

    public function statisticsHotOnlineCourseCategoryIdsAndJoinNum(array $conditions, $limit)
    {
        if (isset($conditions['likeOrgCode']) && CTConst::ROOT_ORG_CODE == $conditions['likeOrgCode']) {
            unset($conditions['likeOrgCode']);
        }

        return $this->getUserDailyLearnRecordJoinUserDao()->statisticsHotOnlineCourseCategoryIdsAndJoinNum($conditions, $limit);
    }

    public function statisticsHotProjectPlanCategoryIdsAndJoinNum(array $conditions, $limit)
    {
        if (isset($conditions['likeOrgCode']) && CTConst::ROOT_ORG_CODE == $conditions['likeOrgCode']) {
            unset($conditions['likeOrgCode']);
        }

        return $this->getProjectPlanMemberJoinUserDao()->statisticsHotProjectPlanCategoryIdsAndJoinNum($conditions, $limit);
    }

    public function statisticsHotOfflineActivityCategoryIdsAndJoinNum(array $conditions, $limit)
    {
        if (isset($conditions['likeOrgCode']) && CTConst::ROOT_ORG_CODE == $conditions['likeOrgCode']) {
            unset($conditions['likeOrgCode']);
        }

        return $this->getOfflineActivityMemberJoinUserDao()->statisticsHotOfflineActivityCategoryIdsAndJoinNum($conditions, $limit);
    }

    public function getOnlineCourseLearnDataForUserLearnDataExtension(array $conditions)
    {
        $usersOnlineCourseLearnData = $this->getUserDailyLearnRecordDao()->calculateLearnDataByUserIdsAndDate($conditions['userIds'],
            $conditions['date']);

        $data = array();
        foreach ($usersOnlineCourseLearnData as $userLearnData) {
            $data[$userLearnData['userId']] = $userLearnData['finishedCourseNum'].'/'.$userLearnData['learnedCourseNum'];
        }

        foreach ($conditions['userIds'] as $userId) {
            if (!isset($data[$userId])) {
                $data[$userId] = '0/0';
            }
        }

        return $data;
    }

    public function getOnlineStudyHoursLearnDataForUserLearnDataExtension(array $conditions)
    {
        $usersOnlineStudyHours = $this->getUserDailyLearnRecordDao()->calculateStudyHoursByUserIdsAndDate($conditions['userIds'],
            $conditions['date']);

        $data = array();
        foreach ($usersOnlineStudyHours as $userOnlineStudyHour) {
            $data[$userOnlineStudyHour['userId']] = $userOnlineStudyHour['studyHours'];
        }

        foreach ($conditions['userIds'] as $userId) {
            if (!isset($data[$userId])) {
                $data[$userId] = 0;
            }
        }

        return $data;
    }

    public function getPostCourseLearnDataForUserLearnDataExtension(array $conditions)
    {
        $postCourseData = $this->getUserDailyLearnRecordDao()->calculatePostCourseLearnDataByUserIdsAndDate($conditions['userIds'],
            $conditions['date']);
        $postCourseData = ArrayToolkit::index($postCourseData, 'userId');

        $users = $this->getUserService()->findUsersByIds($conditions['userIds']);

        $data = array();
        foreach ($conditions['userIds'] as $userId) {
            $finishedPostCourseNum = 0;
            $postCourseNum = 0;
            $learnHours = '0.0';

            if (!empty($users[$userId]['postId'])) {
                $postCourseNum = $this->getPostCourseService()->countPostCourses(array('postId' => $users[$userId]['postId']));
                if (isset($postCourseData[$userId]['finishedPostCourseNum'])) {
                    $finishedPostCourseNum = $postCourseData[$userId]['finishedPostCourseNum'];
                }
                if (isset($postCourseData[$userId]['learnedPostCourseHours'])) {
                    $learnHours = $postCourseData[$userId]['learnedPostCourseHours'];
                }
            }

            $data[$userId] = array(
                'progress' => $finishedPostCourseNum.'/'.$postCourseNum,
                'learnHours' => $learnHours,
            );
        }

        return $data;
    }

    public function getPostCourseLearnProgressForUserLearnDataExtension(array $conditions)
    {
        $postCourseData = $this->getUserDailyLearnRecordDao()->calculatePostCourseLearnDataByUserIdsAndDate($conditions['userIds'],
            $conditions['date']);
        $postCourseData = ArrayToolkit::index($postCourseData, 'userId');

        $users = $this->getUserService()->findUsersByIds($conditions['userIds']);

        $data = array();
        foreach ($conditions['userIds'] as $userId) {
            $finishedPostCourseNum = 0;
            $postCourseNum = 0;

            if (!empty($users[$userId]['postId'])) {
                $postCourseNum = $this->getPostCourseService()->countPostCourses(array('postId' => $users[$userId]['postId']));
                $finishedPostCourseNum = isset($postCourseData[$userId]['finishedPostCourseNum']) ? $postCourseData[$userId]['finishedPostCourseNum'] : $finishedPostCourseNum;
            }

            $data[$userId] = $finishedPostCourseNum.'/'.$postCourseNum;
        }

        return $data;
    }

    public function getPostCourseLearnHourForUserLearnDataExtension(array $conditions)
    {
        $postCourseData = $this->getUserDailyLearnRecordDao()->calculatePostCourseLearnDataByUserIdsAndDate($conditions['userIds'],
            $conditions['date']);
        $postCourseData = ArrayToolkit::index($postCourseData, 'userId');

        $users = $this->getUserService()->findUsersByIds($conditions['userIds']);

        $data = array();
        foreach ($conditions['userIds'] as $userId) {
            $learnHours = '0.0';

            if (!empty($users[$userId]['postId'])) {
                $learnHours = isset($postCourseData[$userId]['learnedPostCourseHours']) ? $postCourseData[$userId]['learnedPostCourseHours'] : $learnHours;
            }

            $data[$userId] = $learnHours;
        }

        return $data;
    }

    /**
     * @return UserDailyLearnRecordDao
     */
    protected function getUserDailyLearnRecordDao()
    {
        return $this->createDao('CorporateTrainingBundle:UserDailyLearnRecord:UserDailyLearnRecordDao');
    }

    /**
     * @return DataStatisticsDaoCacheProxy
     */
    protected function getUserDailyLearnRecordJoinUserDao()
    {
        return new DataStatisticsDaoRedisCacheProxy($this->biz,
            'CorporateTrainingBundle:DataStatistics:UserDailyLearnRecordJoinUserDao');
    }

    /**
     * @return DataStatisticsDaoCacheProxy
     */
    protected function getOfflineCourseTaskJoinResultJoinUserDao()
    {
        return new DataStatisticsDaoRedisCacheProxy($this->biz,
            'CorporateTrainingBundle:DataStatistics:OfflineCourseTaskJoinResultJoinUserDao');
    }

    /**
     * @return DataStatisticsDaoCacheProxy
     */
    protected function getLogJoinUserOrgDao()
    {
        return new DataStatisticsDaoRedisCacheProxy($this->biz,
            'CorporateTrainingBundle:DataStatistics:LogJoinUserOrgDao');
    }

    protected function getUserJoinUserOrgDao()
    {
        return new DataStatisticsDaoRedisCacheProxy($this->biz,
            'CorporateTrainingBundle:DataStatistics:UserJoinUserOrgDao');
    }

    protected function getProjectPlanMemberJoinUserDao()
    {
        return new DataStatisticsDaoRedisCacheProxy($this->biz,
            'CorporateTrainingBundle:DataStatistics:ProjectPlanMemberJoinUserDao');
    }

    protected function getOfflineActivityMemberJoinUserDao()
    {
        return new DataStatisticsDaoRedisCacheProxy($this->biz,
            'CorporateTrainingBundle:DataStatistics:OfflineActivityMemberJoinUserDao');
    }

    /**
     * @return PostCourseService
     */
    protected function getPostCourseService()
    {
        return $this->createService('PostCourse:PostCourseService');
    }

    /**
     * @return \Biz\User\Service\UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
