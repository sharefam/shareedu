<?php

namespace CorporateTrainingBundle\Biz\User\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use CorporateTrainingBundle\Biz\User\Dao\UserOrgDao;

class UserOrgDaoImpl extends GeneralDaoImpl implements UserOrgDao
{
    protected $table = 'user_org';

    public function findByUserId($userId)
    {
        return $this->findByFields(array('userId' => $userId));
    }

    public function findByOrgIds(array $orgIds)
    {
        return $this->findInField('orgId', $orgIds);
    }

    public function declares()
    {
        return array(
            'orderbys' => array(
              'id',
              'createdTime',
              'updatedTime',
            ),
            'timestamps' => array(
                'createdTime',
                'updatedTime',
            ),
            'conditions' => array(
                'id = :id',
                'id IN ( :ids)',
                'orgId = :orgId',
                'orgId In ( :orgIds)',
                'orgCode = :orgCode',
                'userId = :userId',
                'userId IN ( :userIds)',
            ),
        );
    }
}
