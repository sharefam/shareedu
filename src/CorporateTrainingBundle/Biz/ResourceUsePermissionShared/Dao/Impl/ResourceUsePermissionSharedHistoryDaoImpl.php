<?php

namespace CorporateTrainingBundle\Biz\ResourceUsePermissionShared\Dao\Impl;

use CorporateTrainingBundle\Biz\ResourceUsePermissionShared\Dao\ResourceUsePermissionSharedHistoryDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class ResourceUsePermissionSharedHistoryDaoImpl extends AdvancedDaoImpl implements ResourceUsePermissionSharedHistoryDao
{
    protected $table = 'resource_use_permission_shared_history';

    public function findByToUserId($userId)
    {
        return $this->findByFields(array('toUserId' => $userId));
    }

    public function findByFromUserId($userId)
    {
        return $this->findByFields(array('fromUserId' => $userId));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime'),
            'orderbys' => array('createdTime', 'id'),
            'conditions' => array(
                'action = :action',
                'toUserId IN ( :toUserIds )',
                'actionUser = :actionUser',
                'toUserId = :toUserId',
                'fromUserId = :fromUserId',
                'resourceType = :resourceType',
                'resourceId = :resourceId',
            ),
        );
    }
}
