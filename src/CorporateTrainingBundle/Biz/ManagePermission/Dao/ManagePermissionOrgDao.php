<?php

namespace CorporateTrainingBundle\Biz\ManagePermission\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ManagePermissionOrgDao extends GeneralDaoInterface
{
    public function findByUserId($userId);

    public function deleteByUserId($userId);
}
