<?php

namespace CorporateTrainingBundle\Biz\OfflineExam\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use CorporateTrainingBundle\Biz\OfflineExam\Dao\OfflineExamDao;

class OfflineExamDaoImpl extends GeneralDaoImpl implements OfflineExamDao
{
    protected $table = 'offline_exam';

    public function getByIdAndTimeRange($id, $timeRange)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? AND ((? > startTime AND endTime > ?) OR (? <= startTime AND startTime <= ?) OR (? <= endTime AND endTime <= ?))";

        return $this->db()->fetchAll($sql, array($id, $timeRange['startTime'], $timeRange['endTime'], $timeRange['startTime'], $timeRange['endTime'], $timeRange['startTime'], $timeRange['endTime']));
    }

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'orderbys' => array('id', 'createdTime', 'updatedTime'),
            'conditions' => array(
                'id = :id',
                'id IN ( :ids )',
                'projectPlanId = :projectPlanId',
                'title LIKE :titleLike',
                'startTime >= :startTime_GE',
                'startTime <= :startTime_LE',
                'endTime >= :endTime_GE',
                'endTime <= :endTime_LE',
                'status = : status',
            ),
        );
    }
}
