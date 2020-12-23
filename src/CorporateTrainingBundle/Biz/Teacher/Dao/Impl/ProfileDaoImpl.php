<?php

namespace CorporateTrainingBundle\Biz\Teacher\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use CorporateTrainingBundle\Biz\Teacher\Dao\ProfileDao;

class ProfileDaoImpl extends GeneralDaoImpl implements ProfileDao
{
    protected $table = 'teacher_profile';

    public function getByUserId($userId)
    {
        return $this->getByFields(array('userId' => $userId));
    }

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function findByLevelId($levelId)
    {
        return $this->findByFields(array('levelId' => $levelId));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'serializes' => array(
                'teacherProfessionFieldIds' => 'delimiter',
            ),
            'orderbys' => array('id', 'createdTime'),
            'conditions' => array(
                'id IN (:ids)',
                'id NOT IN (:excludeIds)',
                'userId IN (:userIds)',
                'userId = :userId',
                'teacherProfessionFieldIds LIKE :likeTeacherProfessionFieldIds',
                'id = :id',
                'levelId = :levelId',
                'creator = :creator',
            ),
        );
    }
}
