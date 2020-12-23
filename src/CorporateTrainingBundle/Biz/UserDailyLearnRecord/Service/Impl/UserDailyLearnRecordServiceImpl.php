<?php

namespace CorporateTrainingBundle\Biz\UserDailyLearnRecord\Service\Impl;

use Biz\BaseService;
use AppBundle\Common\ArrayToolkit;
use CorporateTrainingBundle\Biz\UserDailyLearnRecord\Dao\UserDailyLearnRecordDao;
use CorporateTrainingBundle\Biz\UserDailyLearnRecord\Service\UserDailyLearnRecordService;

class UserDailyLearnRecordServiceImpl extends BaseService implements UserDailyLearnRecordService
{
    public function getDailyRecord($id)
    {
        return $this->getUserDailyLearnRecordDao()->get($id);
    }

    public function createDailyRecord(array $dailyRecord)
    {
        if (!ArrayToolkit::requireds($dailyRecord, array('userId', 'courseId', 'date'))) {
            throw $this->createInvalidArgumentException('parameters is invalid');
        }

        $fields = $this->filterDailyLearnReportFields($dailyRecord);

        return $this->getUserDailyLearnRecordDao()->create($fields);
    }

    public function createCurrentDateDailyRecord(array $dailyRecord)
    {
        $dailyRecord['date'] = $this->getCurrentDate();

        return $this->createDailyRecord($dailyRecord);
    }

    public function updateDailyRecord($id, array $fields)
    {
        $fields = $this->filterDailyLearnReportFields($fields);

        return $this->getUserDailyLearnRecordDao()->update($id, $fields);
    }

    public function countRecords(array $conditions)
    {
        return $this->getUserDailyLearnRecordDao()->count($conditions);
    }

    public function searchRecords(array $conditions, array $orderBy, $start, $limit)
    {
        return $this->getUserDailyLearnRecordDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function getRecordByUserIdAndCourseId($userId, $courseId)
    {
        return $this->getUserDailyLearnRecordDao()->getByUserIdAndCourseId($userId, $courseId);
    }

    public function getTodayRecordByUserIdAndCourseId($userId, $courseId)
    {
        $currentDate = $this->getCurrentDate();

        return $this->getUserDailyLearnRecordDao()->getByUserIdAndCourseIdAndDate($userId, $courseId, $currentDate);
    }

    public function getRecordByUserIdAndCourseIdAndCourseStatus($userId, $courseId, $status)
    {
        return $this->getUserDailyLearnRecordDao()->getByUserIdAndCourseIdAndCourseStatus($userId, $courseId, $status);
    }

    public function getRecordByUserIdAndCourseIdAndDate($userId, $courseId, $date)
    {
        return $this->getUserDailyLearnRecordDao()->getByUserIdAndCourseIdAndDate($userId, $courseId, $date);
    }

    public function waveLearnTimeById($id, $learnTime)
    {
        return $this->getUserDailyLearnRecordDao()->waveLearnTimeById($id, $learnTime);
    }

    public function waveFinishedTaskNumById($id)
    {
        return $this->getUserDailyLearnRecordDao()->waveFinishedTaskNum($id, 1);
    }

    public function sumPostLearnTimeByUserIdAndPostId($userId, $postId)
    {
        return $this->getUserDailyLearnRecordDao()->sumPostLearnTimeByUserIdAndPostId($userId, $postId);
    }

    public function sumLearnTimeByUserId($userId)
    {
        return $this->getUserDailyLearnRecordDao()->sumLearnTimeByUserId($userId);
    }

    public function sumLearnTimeByConditions(array $conditions)
    {
        return $this->getUserDailyLearnRecordDao()->sumLearnTimeByConditions($conditions);
    }

    public function countFinishedCourseNumByConditions(array $conditions)
    {
        $conditions['courseStatus'] = 1;

        return $this->countRecords($conditions);
    }

    protected function getCurrentDate()
    {
        return strtotime(date('Y-m-d', time()));
    }

    protected function filterDailyLearnReportFields($fields)
    {
        return ArrayToolkit::parts($fields, array(
            'userId',
            'postId',
            'courseId',
            'categoryId',
            'learnTime',
            'finishedTaskNum',
            'courseStatus',
            'classroomId',
            'date',
        ));
    }

    /**
     * @return UserDailyLearnRecordDao
     */
    protected function getUserDailyLearnRecordDao()
    {
        return $this->createDao('CorporateTrainingBundle:UserDailyLearnRecord:UserDailyLearnRecordDao');
    }
}
