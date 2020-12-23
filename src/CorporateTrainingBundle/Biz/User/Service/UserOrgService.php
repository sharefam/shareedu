<?php

namespace CorporateTrainingBundle\Biz\User\Service;

interface UserOrgService
{
    public function getUserOrg($id);

    public function findUserOrgsByUserId($userId);

    public function findUserOrgsByOrgIds(array $orgIds);

    public function createUserOrg(array $userOrg);

    public function updateUserOrg($id, array $fields);

    public function countUserOrgs(array $conditions);

    public function searchUserOrgs(array $conditions, array $orderBys, $start, $limit);

    public function deleteUserOrg($id);

    public function setUserOrgs($userId, array $orgs);
}
