<?php

namespace CorporateTrainingBundle\Biz\ResourceUsePermissionShared\Dao\Impl;

use CorporateTrainingBundle\Biz\ResourceUsePermissionShared\Dao\ResourceUsePermissionSharedDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class ResourceUsePermissionSharedDaoImpl extends AdvancedDaoImpl implements ResourceUsePermissionSharedDao
{
    protected $table = 'resource_use_permission_shared';

    public function findByToUserId($userId)
    {
        return $this->findByFields(array('toUserId' => $userId));
    }

    public function findByFromUserId($userId)
    {
        return $this->findByFields(array('fromUserId' => $userId));
    }

    public function getByResourceIdAndResourceTypeAndToUserId($resourceId, $resourceType, $toUserId)
    {
        return $this->getByFields(array('resourceId' => $resourceId, 'resourceType' => $resourceType, 'toUserId' => $toUserId));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime'),
            'orderbys' => array('createdTime', 'id'),
            'conditions' => array(
                'toUserId = :toUserId',
                'toUserId IN ( :toUserIds )',
                'resourceId NOT IN ( :excludeResourceIds )',
                'fromUserId = :fromUserId',
                'resourceType = :resourceType',
                'resourceId = :resourceId',
            ),
        );
    }
}
