<?php

namespace CorporateTrainingBundle\Biz\User\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use CorporateTrainingBundle\Biz\User\Service\UserOrgService;

class UserOrgServiceImpl extends BaseService implements UserOrgService
{
    public function getUserOrg($id)
    {
        return $this->getUserOrgDao()->get($id);
    }

    public function findUserOrgsByUserId($userId)
    {
        return $this->getUserOrgDao()->findByUserId($userId);
    }

    public function findUserOrgsByOrgIds(array $orgIds)
    {
        return $this->getUserOrgDao()->findByOrgIds($orgIds);
    }

    public function createUserOrg(array $userOrg)
    {
        if (!ArrayToolkit::requireds($userOrg, array('userId', 'orgId', 'orgCode'))) {
            throw $this->createInvalidArgumentException('Lack Of Required Fields');
        }
        $fields = $this->filterUserOrg($userOrg);

        return $this->getUserOrgDao()->create($fields);
    }

    public function updateUserOrg($id, array $fields)
    {
        $fields = $this->filterUserOrg($fields);

        return $this->getUserOrgDao()->update($id, $fields);
    }

    public function countUserOrgs(array $conditions)
    {
        return $this->getUserOrgDao()->count($conditions);
    }

    public function searchUserOrgs(array $conditions, array $orderBys, $start, $limit)
    {
        return $this->getUserOrgDao()->search($conditions, $orderBys, $start, $limit);
    }

    public function deleteUserOrg($id)
    {
        return $this->getUserOrgDao()->delete($id);
    }

    public function setUserOrgs($userId, array $orgs)
    {
        $existUserOrgs = $this->findUserOrgsByUserId($userId);

        foreach ($existUserOrgs as $existUserOrg) {
            $this->deleteUserOrg($existUserOrg['id']);
        }

        foreach ($orgs as $org) {
            if (!empty($org)) {
                $userOrg = array(
                    'userId' => $userId,
                    'orgId' => $org['id'],
                    'orgCode' => $org['orgCode'],
                );

                $this->createUserOrg($userOrg);
            }
        }
    }

    protected function filterUserOrg($fields)
    {
        return ArrayToolkit::parts($fields, array(
            'userId',
            'orgId',
            'orgCode',
        ));
    }

    /**
     * @return \CorporateTrainingBundle\Biz\User\Dao\UserOrgDao
     */
    protected function getUserOrgDao()
    {
        return $this->createDao('User:UserOrgDao');
    }
}
