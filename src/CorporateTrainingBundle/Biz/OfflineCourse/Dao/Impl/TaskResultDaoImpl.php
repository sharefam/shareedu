<?php

namespace CorporateTrainingBundle\Biz\OfflineCourse\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use CorporateTrainingBundle\Biz\OfflineCourse\Dao\TaskResultDao;

class TaskResultDaoImpl extends GeneralDaoImpl implements TaskResultDao
{
    protected $table = 'offline_course_task_result';

    public function getByTaskIdAndUserId($taskId, $userId)
    {
        return $this->getByFields(array('taskId' => $taskId, 'userId' => $userId));
    }

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function findByOfflineCourseId($offlineCourseId)
    {
        return $this->findByFields(array('offlineCourseId' => $offlineCourseId));
    }

    public function findByUserId($userId)
    {
        return $this->findByFields(array('userId' => $userId));
    }

    public function deleteByTaskId($taskId)
    {
        return $this->db()->delete($this->table, array('taskId' => $taskId));
    }

    public function findHomeworkStatusNumGroupByStatus($taskId, $userIds)
    {
        if (empty($userIds)) {
            return array();
        }
        $marks = str_repeat('?,', count($userIds) - 1).'?';
        $sql = "SELECT homeworkStatus,COUNT(id) AS num FROM {$this->table} WHERE taskId=? AND userId IN ({$marks}) GROUP BY homeworkStatus";

        return $this->db()->fetchAll($sql, array_merge(array($taskId), $userIds)) ?: array();
    }

    public function calculateUsersOfflineLearnTime($userIds, $offlineCourseIds, $endDateRange)
    {
        $userMarks = str_repeat('?,', count($userIds) - 1).'?';
        $offlineCourseMarks = str_repeat('?,', count($offlineCourseIds) - 1).'?';

        $sql = "SELECT r.userId AS userId, SUM(t.endTime - t.startTime) AS offlineStudyTime FROM `offline_course_task_result` r LEFT JOIN `offline_course_task` t ON r.taskId = t.id WHERE r.offlineCourseId IN ({$offlineCourseMarks}) AND r.userId IN ({$userMarks}) AND (t.endTime >= ? AND t.endTime <= ?) AND r.status = 'finish' GROUP BY r.userId";

        $parameters = array_merge($offlineCourseIds, $userIds, array_values($endDateRange));

        return $this->db()->fetchAll($sql, $parameters);
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'orderbys' => array('id', 'createdTime', 'updatedTime'),
            'conditions' => array(
                'id = :id',
                'offlineCourseId = :offlineCourseId',
                'userId = :userId',
                'taskId = :taskId',
                'status = :status',
                'attendStatus = :attendStatus',
                'attendStatus IN ( :attendStatuses)',
                'homeworkStatus = :homeworkStatus',
                'homeworkStatus IN ( :homeworkStatuses)',
                'id IN ( :ids )',
                'userId IN ( :userIds )',
                'offlineCourseId IN ( :offlineCourseIds )',
                'status IN ( :statuses)',
                'taskId IN ( :taskIds )',
                'createdTime <= :createdTime_LE',
                'createdTime >= :createdTime_GE',
            ),
        );
    }
}
