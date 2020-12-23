<?php

namespace CorporateTrainingBundle\Biz\UserDailyLearnRecord\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use CorporateTrainingBundle\Biz\UserDailyLearnRecord\Dao\UserDailyLearnRecordDao;

class UserDailyLearnRecordDaoImpl extends GeneralDaoImpl implements UserDailyLearnRecordDao
{
    protected $table = 'user_daily_learn_record';

    public function getByUserIdAndCourseId($userId, $courseId)
    {
        return $this->getByFields(array('userId' => $userId, 'courseId' => $courseId));
    }

    public function getByUserIdAndCourseIdAndCourseStatus($userId, $courseId, $courseStatus)
    {
        return $this->getByFields(array('userId' => $userId, 'courseId' => $courseId, 'courseStatus' => $courseStatus));
    }

    public function waveLearnTimeById($id, $learnTime)
    {
        return $this->wave(array($id), array('learnTime' => $learnTime));
    }

    public function waveFinishedTaskNum($id, $finishedTaskNum)
    {
        return $this->wave(array($id), array('finishedTaskNum' => $finishedTaskNum));
    }

    public function getByUserIdAndCourseIdAndDate($userId, $courseId, $date)
    {
        return $this->getByFields(array('userId' => $userId, 'courseId' => $courseId, 'date' => $date));
    }

    public function sumLearnTimeByUserId($userId)
    {
        $sql = "SELECT SUM(learnTime) FROM {$this->table} WHERE userId = ?";

        return $this->db()->fetchColumn($sql, array($userId)) ?: 0;
    }

    public function sumPostLearnTimeByUserIdAndPostId($userId, $postId)
    {
        $sql = "SELECT SUM(learnTime) FROM {$this->table} WHERE userId = ? AND postId = ?";

        return $this->db()->fetchColumn($sql, array($userId, $postId)) ?: 0;
    }

    public function sumLearnTimeByConditions($conditions)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select('SUM(learnTime)');

        return $builder->execute()->fetchColumn() ?: 0;
    }

    public function calculateLearnDataByUserIdsAndDate(array $userIds, $date)
    {
        $userMarks = str_repeat('?,', count($userIds) - 1).'?';

        $sql = "SELECT userId, COUNT(DISTINCT courseId) AS learnedCourseNum, COUNT(IF(courseStatus = 1,true,null)) as finishedCourseNum FROM `user_daily_learn_record` WHERE userId IN ({$userMarks}) AND (date >= ? AND date <= ? ) GROUP BY userId";

        $parameters = array_merge($userIds, array_values($date));

        return $this->db()->fetchAll($sql, $parameters);
    }

    public function calculateStudyHoursByUserIdsAndDate(array $userIds, $date)
    {
        $userMarks = str_repeat('?,', count($userIds) - 1).'?';

        $sql = "SELECT r.userId, FORMAT((SUM(r.learnTime)/3600), 1) AS studyHours FROM `user_daily_learn_record` r WHERE r.userId IN ({$userMarks}) AND (r.date >= ? AND r.date <= ?) GROUP BY r.userId";

        $parameters = array_merge($userIds, array_values($date));

        return $this->db()->fetchAll($sql, $parameters);
    }

    public function calculatePostCourseLearnDataByUserIdsAndDate(array $userIds, $date)
    {
        $userMarks = str_repeat('?,', count($userIds) - 1).'?';

        $sql = "SELECT COUNT(IF(r.courseStatus = 1,true,null)) AS finishedPostCourseNum, FORMAT((SUM(r.learnTime)/3600), 1) AS learnedPostCourseHours, r.userId AS userId, r.postId AS postId FROM `user_daily_learn_record` r LEFT JOIN `user` u ON r.userId = u.id AND r.postId = u.postId WHERE r.userId IN ({$userMarks}) AND (r.date >= ? AND r.date <= ?) AND u.postId != 0 AND r.postId != 0 GROUP BY r.userId";

        $parameters = array_merge($userIds, array_values($date));

        return $this->db()->fetchAll($sql, $parameters);
    }

    public function statisticsLearnRecordGroupByOrgId(array $conditions)
    {
        $recordMarks = str_repeat('?,', count($conditions['recordIds']) - 1).'?';

        $orgMarks = str_repeat('?,', count($conditions['orgIds']) - 1).'?';
        $sql = "SELECT u.orgId, COUNT(DISTINCT r.userId) AS learnUserNum, SUM(r.learnTime) AS totalLearnTime ,SUM(r.finishedTaskNum) AS totalFinishedTaskNum, COUNT(IF(r.courseStatus = 1,true,null)) AS finishedCourseNum FROM `user_org` u LEFT JOIN `user_daily_learn_record` r ON u.userId = r.userId AND r.id IN ({$recordMarks}) WHERE 
        u.orgId IN ({$orgMarks}) GROUP BY u.orgId";

        $parameters = array_merge($conditions['recordIds'], $conditions['orgIds']);

        return $this->db()->fetchAll($sql, $parameters);
    }

    public function statisticsLearnRecordGroupByPostId(array $conditions)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select('postId, COUNT(DISTINCT userId) AS learnUserNum, SUM(learnTime) AS totalLearnTime, SUM(finishedTaskNum) AS totalFinishedTaskNum, COUNT(IF(courseStatus = 1,true,null)) AS finishedCourseNum')
            ->groupBy('postId');

        return $builder->execute()->fetchAll() ?: array();
    }

    public function statisticsLearnRecordGroupByCategoryId(array $conditions)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select('categoryId, COUNT(DISTINCT userId) AS learnUserNum, SUM(learnTime) AS totalLearnTime ,SUM(finishedTaskNum) AS totalFinishedTaskNum, COUNT(IF(courseStatus = 1,true,null)) AS finishedCourseNum')
            ->groupBy('categoryId');

        return $builder->execute()->fetchAll() ?: array();
    }

    public function statisticsOrgLearnTimeRankingList(array $conditions, $start = 0, $limit = 5)
    {
        $sql = 'SELECT u.orgId, COUNT(DISTINCT r.userId) AS learnUserNum, SUM(r.learnTime) AS totalLearnTime FROM user_org  u LEFT JOIN user_daily_learn_record r ON u.userId =r.userId WHERE (date >= ? AND date <= ?) group by u.orgId ORDER BY totalLearnTime DESC LIMIT 5';

        return $this->db()->fetchAll($sql, array($conditions['startDateTime'], $conditions['endDateTime']));
    }

    public function statisticsLearnUsersNumGroupByDate(array $conditions)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select('COUNT(DISTINCT userId) AS learnedUserNum, date')
            ->groupBy('date');

        return $builder->execute()->fetchAll() ?: array();
    }

    public function statisticsTotalLearnTimeGroupByDate(array $conditions)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select('date, SUM(learnTime) AS totalLearnTime')
            ->groupBy('date');

        return $builder->execute()->fetchAll() ?: array();
    }

    public function declares()
    {
        return array(
            'timestamps' => array(
                'createdTime',
                'updatedTime',
            ),
            'orderbys' => array(
                'id',
                'createdTime',
                'updatedTime',
            ),
            'conditions' => array(
                'date = :date',
                'id =: id',
                'userId = :userId',
                'categoryId = :categoryId',
                'postId = :postId',
                'date >= :startDateTime',
                'date <= :endDateTime',
                'courseId = :courseId',
                'userId IN ( :userIds)',
                'userId NOT IN ( :excludeUserIds )',
                'postId IN ( :postIds)',
                'categoryId IN ( :categoryIds)',
                'courseStatus = :courseStatus',
            ),
        );
    }
}
