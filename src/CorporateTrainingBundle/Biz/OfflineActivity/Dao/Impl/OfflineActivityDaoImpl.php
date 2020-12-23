<?php

namespace CorporateTrainingBundle\Biz\OfflineActivity\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use CorporateTrainingBundle\Biz\OfflineActivity\Dao\OfflineActivityDao;

class OfflineActivityDaoImpl extends GeneralDaoImpl implements OfflineActivityDao
{
    protected $table = 'offline_activity';

    public function findOfflineActivitiesByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function findByCategoryId($categoryId)
    {
        return $this->findByFields(array('categoryId' => $categoryId));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'serializes' => array('cover' => 'json'),
            'orderbys' => array('id', 'createdTime', 'updatedTime', 'enrollmentEndDate', 'enrollmentStartDate', 'endTime', 'startTime'),
            'conditions' => array(
                'title LIKE :title',
                'categoryId = :categoryId',
                'parentId = :parentId',
                'orgCode = :orgCode',
                'orgCode LIKE :likeOrgCode',
                'startTime >= :startTime_GE',
                'startTime > :startTime_GT',
                'startTime <= :startTime_LE',
                'startTime < :startTime_LT',
                'endTime >= :endTime_GE',
                'endTime > :endTime_GT',
                'endTime <= :endTime_LE',
                'endTime < :endTime_LT',
                'enrollmentEndDate >= :enrollmentEndDate_GE',
                'enrollmentEndDate <= :enrollmentEndDate_LE',
                'enrollmentStartDate >= :enrollmentStartDate_GE',
                'enrollmentStartDate <= :enrollmentStartDate_LE',
                'status = :status',
                'categoryId IN ( :categoryIds)',
                'id IN ( :ids)',
                'creator = :creator',
                'orgId IN ( :orgIds )',
            ),
        );
    }
}
