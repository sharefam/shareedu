<?php

namespace CorporateTrainingBundle\Biz\ManagePermission\Dao\Impl;

use CorporateTrainingBundle\Biz\ManagePermission\Dao\ManagePermissionOrgDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ManagePermissionOrgDaoImpl extends GeneralDaoImpl implements ManagePermissionOrgDao
{
    protected $table = 'manage_permission_org';

    public function findByUserId($userId)
    {
        return $this->findByFields(array('userId' => $userId));
    }

    public function deleteByUserId($userId)
    {
        return $this->db()->delete($this->table, array('userId' => $userId));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime'),
            'orderbys' => array('createdTime', 'id'),
            'conditions' => array(
                'userId = :userId',
                'orgId = :orgId',
                'createdUserId = :createdUserId',
                'userId IN ( :userIds)',
                'orgId IN ( :orgIds)',
            ),
        );
    }
}
