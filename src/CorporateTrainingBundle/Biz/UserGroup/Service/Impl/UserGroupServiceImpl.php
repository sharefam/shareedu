<?php

namespace CorporateTrainingBundle\Biz\UserGroup\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use CorporateTrainingBundle\Biz\UserGroup\Service\UserGroupService;

class UserGroupServiceImpl extends BaseService implements UserGroupService
{
    public function createUserGroup($fields)
    {
        $this->validateUserGroupFields($fields);

        if ($this->getUserGroupByName($fields['name'])) {
            throw $this->createServiceException('User Group already exists');
        }

        $fields = $this->filterUserGroupFields($fields);

        return  $this->getUserGroupDao()->create($fields);
    }

    public function updateUserGroup($id, $fields)
    {
        $this->checkUserGroupExist($id);
        $this->validateUserGroupFields($fields);
        $fields = $this->filterUserGroupFields($fields);

        return $this->getUserGroupDao()->update($id, $fields);
    }

    public function deleteUserGroup($id)
    {
        $this->checkUserGroupExist($id);
        $userGroup = $this->getUserGroup($id);

        return $this->getUserGroupDao()->delete($userGroup['id']);
    }

    public function getUserGroup($id)
    {
        return $this->getUserGroupDao()->get($id);
    }

    public function getUserGroupByName($name)
    {
        return $this->getUserGroupDao()->getByName($name);
    }

    public function findUserGroupsByIds($ids)
    {
        return $this->getUserGroupDao()->findByIds($ids);
    }

    public function findAllUserGroups()
    {
        return $this->searchUserGroups(array(), array('createdTime' => 'DESC'), 0, PHP_INT_MAX);
    }

    public function countUserGroup($conditions)
    {
        return $this->getUserGroupDao()->count($conditions);
    }

    public function searchUserGroups($conditions, $orderBy, $start, $limit)
    {
        return $this->getUserGroupDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function isUserGroupNameAvailable($name, $exclude)
    {
        if (empty($name)) {
            return false;
        }

        if ($name == $exclude) {
            return true;
        }

        $userGroup = $this->getUserGroupByName($name);

        return $userGroup ? false : true;
    }

    public function isUserGroupCodeAvailable($code, $exclude)
    {
        $post = $this->getUserGroupDao()->getByCode($code);

        if (empty($post)) {
            return true;
        }

        return $post['code'] === $exclude;
    }

    protected function validateUserGroupFields($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('name', 'code'), true)) {
            throw $this->createInvalidArgumentException('Lack of required fields');
        }
    }

    protected function filterUserGroupFields($fields)
    {
        return ArrayToolkit::parts($fields, array('name', 'description', 'code', 'createdUserId'));
    }

    protected function checkUserGroupExist($id)
    {
        $userGroup = $this->getUserGroup($id);

        if (empty($userGroup)) {
            throw $this->createNotFoundException("UserGroup#{$id} Not Exist");
        }
    }

    protected function getUserGroupDao()
    {
        return $this->biz->dao('CorporateTrainingBundle:UserGroup:UserGroupDao');
    }
}
