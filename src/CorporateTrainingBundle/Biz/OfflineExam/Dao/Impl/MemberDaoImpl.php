<?php

namespace CorporateTrainingBundle\Biz\OfflineExam\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use CorporateTrainingBundle\Biz\OfflineExam\Dao\MemberDao;

class MemberDaoImpl extends GeneralDaoImpl implements MemberDao
{
    protected $table = 'offline_exam_member';

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function findByOfflineExamId($offlineExamId)
    {
        return $this->findByFields(array('offlineExamId' => $offlineExamId));
    }

    public function getByOfflineExamIdAndUserId($offlineExamId, $userId)
    {
        return $this->getByFields(array('offlineExamId' => $offlineExamId, 'userId' => $userId));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'orderbys' => array('id', 'createdTime', 'updatedTime'),
            'conditions' => array(
                'id = :id',
                'id IN ( :ids )',
                'offlineExamId = :offlineExamId',
                'offlineExamId IN ( :offlineExamIds)',
                'userId IN ( :userIds)',
                'userId = :userId',
                'status = :status',
            ),
        );
    }
}
