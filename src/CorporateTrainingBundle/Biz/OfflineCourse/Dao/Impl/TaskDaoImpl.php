<?php

namespace CorporateTrainingBundle\Biz\OfflineCourse\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use CorporateTrainingBundle\Biz\OfflineCourse\Dao\TaskDao;

class TaskDaoImpl extends GeneralDaoImpl implements TaskDao
{
    protected $table = 'offline_course_task';

    public function getByActivityId($activityId)
    {
        return $this->getByFields(array('activityId' => $activityId));
    }

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function findByOfflineCourseId($offlineCourseId)
    {
        return $this->findByFields(array('offlineCourseId' => $offlineCourseId));
    }

    public function findByOfflineCourseIdAndTypeAndTimeRange($offlineCourseId, $type, $timeRange)
    {
        $sql = "SELECT * FROM {$this->table} WHERE offlineCourseId = ? AND type = ? AND ((? > startTime AND endTime > ?) OR (? <= startTime AND startTime <= ?) OR (? <= endTime AND endTime <= ?))";

        return $this->db()->fetchAll($sql, array($offlineCourseId, $type, $timeRange['startTime'], $timeRange['endTime'], $timeRange['startTime'], $timeRange['endTime'], $timeRange['startTime'], $timeRange['endTime']));
    }

    public function findPersonLearnTimeRankingList($userIds, $courseIds, $timeRange, $limit)
    {
        $limit = (int) $limit;
        $marks = str_repeat('?,', count($userIds) - 1).'?';
        $courseMarks = str_repeat('?,', count($courseIds) - 1).'?';

        $sql = "SELECT r.userId, SUM(t.endTime - t.startTime) AS totalLearnTime FROM {$this->table} t LEFT JOIN offline_course_task_result r on t.id = r.taskId WHERE r.attendStatus = ? AND r.userId IN ({$marks}) AND r.offlineCourseId IN ({$courseMarks}) AND (? <= t.startTime) AND ( t.startTime <= ?) GROUP BY r.userId ORDER BY totalLearnTime DESC limit {$limit}";

        return $this->db()->fetchAll($sql, array_merge(array('attended'), $userIds, $courseIds, array($timeRange['startTime']), array($timeRange['endTime'])) ?: array());
    }

    public function countStartTimeAndEndTimeAllInOfflineCourseTime($timeRange, $courseIds)
    {
        $offlineCourseMarks = str_repeat('?,', count($courseIds) - 1).'?';

        $sql = "SELECT SUM(t.endTime - t.startTime) AS offlineCourseTime FROM {$this->table} t WHERE t.offlineCourseId IN ({$offlineCourseMarks}) AND (t.startTime >= ? AND t.endTime <= ?) AND type = 'offlineCourse'";

        $parameters = array_merge($courseIds, array_values($timeRange));

        return $this->db()->fetchAll($sql, $parameters);
    }

    public function countStartTimeEarlierThanSearchStartTimeOfflineCourseTime($timeRange, $courseIds)
    {
        $offlineCourseMarks = str_repeat('?,', count($courseIds) - 1).'?';

        $sql = "SELECT SUM(t.endTime - ?) AS offlineCourseTime FROM {$this->table} t WHERE t.offlineCourseId IN ({$offlineCourseMarks}) AND (t.startTime < ? AND t.endTime > ? AND t.endTime < ?) AND type = 'offlineCourse'";

        $parameters = array_merge(array($timeRange['startTime']), $courseIds, array($timeRange['startTime']), array_values($timeRange));

        return $this->db()->fetchAll($sql, $parameters);
    }

    public function countEndTimeLaterThanSearchEndTimeOfflineCourseTime($timeRange, $courseIds)
    {
        $offlineCourseMarks = str_repeat('?,', count($courseIds) - 1).'?';

        $sql = "SELECT SUM(? - t.startTime) AS offlineCourseTime FROM {$this->table} t WHERE t.offlineCourseId IN ({$offlineCourseMarks}) AND (t.startTime > ? AND t.startTime < ? AND t.endTime > ?) AND type = 'offlineCourse'";

        $parameters = array_merge(array($timeRange['endTime']), $courseIds, array_values($timeRange), array($timeRange['endTime']));

        return $this->db()->fetchAll($sql, $parameters);
    }

    public function countStartTimeAndEndTimeAllOutOfflineCourseTime($timeRange, $courseIds)
    {
        $offlineCourseMarks = str_repeat('?,', count($courseIds) - 1).'?';

        $sql = "SELECT SUM(? - ?) AS offlineCourseTime FROM {$this->table} t WHERE t.offlineCourseId IN ({$offlineCourseMarks}) AND (t.startTime < ? AND t.endTime > ?) AND type = 'offlineCourse'";

        $parameters = array_merge(array($timeRange['endTime']), array($timeRange['startTime']), $courseIds, array_values($timeRange));

        return $this->db()->fetchAll($sql, $parameters);
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'orderbys' => array('id', 'createdTime', 'updatedTime', 'startTime', 'seq'),
            'conditions' => array(
                'id = :id',
                'offlineCourseId = :offlineCourseId',
                'offlineCourseId IN ( :offlineCourseIds )',
                'status = :status',
                'type = :type',
                'id IN ( :ids )',
                'startTime >= :startTime_GE',
                'startTime <= :startTime_LE',
                'endTime >= :endTime_GE',
                'endTime <= :endTime_LE',
                'status IN ( :statuses )',
                'hasHomework = :hasHomework',
                'orgId = :orgId',
                'orgId IN ( :orgIds )',
            ),
        );
    }
}
