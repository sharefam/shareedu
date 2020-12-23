<?php

namespace CorporateTrainingBundle\Biz\ResourceUsePermissionShared\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface ResourceUsePermissionSharedDao extends AdvancedDaoInterface
{
    public function findByToUserId($userId);

    public function findByFromUserId($userId);

    public function getByResourceIdAndResourceTypeAndToUserId($resourceId, $resourceType, $toUserId);
}
