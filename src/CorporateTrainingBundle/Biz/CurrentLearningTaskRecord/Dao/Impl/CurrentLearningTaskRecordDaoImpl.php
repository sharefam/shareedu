<?php

namespace CorporateTrainingBundle\Biz\CurrentLearningTaskRecord\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use CorporateTrainingBundle\Biz\CurrentLearningTaskRecord\Dao\CurrentLearningTaskRecordDao;

class CurrentLearningTaskRecordDaoImpl extends GeneralDaoImpl implements CurrentLearningTaskRecordDao
{
    protected $table = 'current_learning_task_record';

    public function getByUserId($userId)
    {
        return $this->getByFields(array('userId' => $userId));
    }

    public function getForUpdate($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? FOR UPDATE";

        return $this->db()->fetchAssoc($sql, array($id));
    }

    public function declares()
    {
        return array(
            'serializes' => array(),
            'orderbys' => array('id', 'createdTime'),
            'timestamps' => array('createdTime', 'updatedTime'),
            'conditions' => array(
                'id =: id',
                'id IN ( :ids )',
                'taskId = :taskId',
                'userId = :userId',
            ),
        );
    }
}
