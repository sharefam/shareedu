<?php

namespace CorporateTrainingBundle\Biz\ResourceUsePermissionShared\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface ResourceUsePermissionSharedHistoryDao extends AdvancedDaoInterface
{
    public function findByToUserId($userId);

    public function findByFromUserId($userId);
}
