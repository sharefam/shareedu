<?php

namespace CorporateTrainingBundle\Biz\UserGroup\Service;

interface UserGroupService
{
    public function createUserGroup($fields);

    public function updateUserGroup($id, $fields);

    public function deleteUserGroup($id);

    public function getUserGroup($id);

    public function getUserGroupByName($id);

    public function findUserGroupsByIds($ids);

    public function countUserGroup($conditions);

    public function searchUserGroups($conditions, $orderBy, $start, $limit);

    public function isUserGroupNameAvailable($name, $exclude);

    public function isUserGroupCodeAvailable($code, $exclude);
}
