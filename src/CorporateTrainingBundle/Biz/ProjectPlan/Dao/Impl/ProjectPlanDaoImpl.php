<?php

namespace CorporateTrainingBundle\Biz\ProjectPlan\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use CorporateTrainingBundle\Biz\ProjectPlan\Dao\ProjectPlanDao;

class ProjectPlanDaoImpl extends GeneralDaoImpl implements ProjectPlanDao
{
    protected $table = 'project_plan';

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function findByCreatedUserId($createdUserId)
    {
        return $this->findByFields(array('createdUserId' => $createdUserId));
    }

    public function findByCategoryId($categoryId)
    {
        return $this->findByFields(array('categoryId' => $categoryId));
    }

    public function findMonthlyProjectPlanIdsByOrgAndCategory($date, $orgCode, $categoryId)
    {
        $sql = "SELECT DISTINCT id FROM {$this->table} WHERE status <> 'unpublished' AND orgCode like ? AND ((startTime >= ? AND startTime <= ?) OR (endTime >= ? AND endTime <= ?) OR (startTime <= ? AND endTime >= ?))";
        $parameters = array_merge(array($orgCode.'%'), array_values($date), array_values($date), array_values($date));

        if (!empty($categoryId)) {
            $sql .= 'AND categoryId = ?';
            $parameters = array_merge($parameters, array($categoryId));
        }

        return $this->db()->fetchAll($sql, $parameters);
    }

    public function findByDateAndIds($date, $ids, $start, $limit)
    {
        $idMarks = str_repeat('?,', count($ids) - 1).'?';

        $sql = "SELECT * FROM {$this->table} WHERE id IN ({$idMarks}) AND ((startTime >= ? AND startTime <= ?) OR (endTime >= ? AND endTime <= ?) OR (startTime <= ? AND endTime >= ?)) limit {$start},{$limit}";

        $parameters = array_merge($ids, array_values($date), array_values($date), array_values($date));

        return $this->db()->fetchAll($sql, $parameters);
    }

    public function countByDateAndIds($date, $ids)
    {
        $idMarks = str_repeat('?,', count($ids) - 1).'?';

        $sql = "SELECT count(id) FROM {$this->table} WHERE id IN ({$idMarks}) AND ((startTime >= ? AND startTime <= ?) OR (endTime >= ? AND endTime <= ?) OR (startTime <= ? AND endTime >= ?))";

        $parameters = array_merge($ids, array_values($date), array_values($date), array_values($date));

        return $this->db()->fetchColumn($sql, $parameters);
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'orderbys' => array('id', 'createdTime', 'updatedTime', 'enrollmentStartDate', 'enrollmentEndDate', 'startTime', 'endTime'),
            'serializes' => array('cover' => 'json'),
            'conditions' => array(
                'requireEnrollment = :requireEnrollment',
                'categoryId = :categoryId',
                'id = :id',
                'status = :status',
                'status NOT IN ( :excludeStatus )',
                'name LIKE :nameLike',
                'createdUserId = :createdUserId',
                'startTime >= :startTime_GE',
                'startTime > :startTime_GT',
                'startTime <= :startTime_LE',
                'startTime < :startTime_LT',
                'endTime >= :endTime_GE',
                'endTime > :endTime_GT',
                'endTime <= :endTime_LE',
                'endTime < :endTime_LT',
                'enrollmentStartDate <= :enrollmentStartDate_LE',
                'enrollmentEndDate <= :enrollmentEndDate_LE',
                'enrollmentStartDate >= :enrollmentStartDate_GE',
                'enrollmentEndDate >= :enrollmentEndDate_GE',
                'id IN ( :ids )',
                'orgId = :orgId',
                'orgId IN ( :orgIds )',
                'orgCode = :orgCode',
                'orgCode Like :likeOrgCode',
                'itemNum = :itemNum',
                'createdTime >= createdTime_GE',
                'createdUserId = :createdUserId',
            ),
        );
    }
}
