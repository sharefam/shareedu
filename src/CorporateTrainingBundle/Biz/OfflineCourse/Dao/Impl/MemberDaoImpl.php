<?php

namespace CorporateTrainingBundle\Biz\OfflineCourse\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use CorporateTrainingBundle\Biz\OfflineCourse\Dao\MemberDao;

class MemberDaoImpl extends GeneralDaoImpl implements MemberDao
{
    protected $table = 'offline_course_member';

    public function getByOfflineCourseIdAndUserId($offlineCourseId, $userId)
    {
        return $this->getByFields(array('offlineCourseId' => $offlineCourseId, 'userId' => $userId));
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

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'orderbys' => array('id', 'createdTime', 'updatedTime'),
            'conditions' => array(
                'id = :id',
                'offlineCourseId = :offlineCourseId',
                'offlineCourseId IN ( :offlineCourseIds )',
                'userId = :userId',
                'id IN ( :ids )',
                'userId IN ( :userIds )',
                'learnedNum = :learnedNum',
                'learnedNum >= :learnedNumGreaterThan',
                'learnedNum < :learnedNumLessThan',
            ),
        );
    }
}
