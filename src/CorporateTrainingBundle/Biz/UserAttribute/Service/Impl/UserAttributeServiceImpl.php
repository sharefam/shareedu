<?php

namespace CorporateTrainingBundle\Biz\UserAttribute\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Org\Service\OrgService;
use Biz\User\Service\UserService;
use CorporateTrainingBundle\Biz\Post\Service\PostService;
use CorporateTrainingBundle\Biz\User\Service\UserOrgService;
use CorporateTrainingBundle\Biz\UserAttribute\Service\UserAttributeService;
use CorporateTrainingBundle\Biz\UserGroup\Service\MemberService;
use CorporateTrainingBundle\Biz\UserGroup\Service\UserGroupService;

class UserAttributeServiceImpl extends BaseService implements UserAttributeService
{
    protected $searchLimit = 3;

    public function findDistinctUserIdsByAttributes(array $attributes, $orgIds = array())
    {
        $userIds = array();
        if (empty($attributes)) {
            return $userIds;
        }

        $groupAttributes = ArrayToolkit::group($attributes, 'attributeType');
        foreach ($groupAttributes as $key => $groupAttribute) {
            $func = $this->getFindUserIdsFuncName($key);
            $attributeUserIds = $this->$func($groupAttribute);
            $userIds = array_merge($attributeUserIds, $userIds);
        }

        $userIds = array_unique($userIds);

        if (empty($userIds)) {
            return array();
        }

        $userOrgs = $this->getUserOrgService()->searchUserOrgs(
            array('userIds' => $userIds, 'orgIds' => $orgIds),
            array(),
            0,
            PHP_INT_MAX
        );
        $userIds = ArrayToolkit::column($userOrgs, 'userId');

        return array_unique($userIds);
    }

    public function searchAttributesName(array $attributes, $likeName, $orgIds = array())
    {
        $name = array();

        if (empty($attributes) || empty($likeName)) {
            return $name;
        }

        foreach ($attributes as $attribute) {
            $func = $this->getSearchAttributeNameFuncName($attribute);
            $attributeName = $this->$func($likeName, $orgIds);
            $name = array_merge($attributeName, $name);
        }

        return $name;
    }

    protected function getFindUserIdsFuncName($attributeType)
    {
        return 'findUserIdsBy'.ucfirst($attributeType);
    }

    protected function findUserIdsByOrg($orgAttributes)
    {
        $orgIds = ArrayToolkit::column($orgAttributes, 'attributeId');
        if (empty($orgIds)) {
            return array();
        }

        $userIds = $this->findUserIdsByOrgIds($orgIds);
        if (empty($userIds)) {
            return array();
        }

        $users = $this->getUserService()->searchUsers(
            array('userIds' => $userIds, 'locked' => 0, 'noType' => 'system'),
            array('id' => 'DESC'),
            0,
            PHP_INT_MAX
        );
        if (empty($users)) {
            return array();
        }

        return ArrayToolkit::column($users, 'id');
    }

    protected function findUserIdsByPost($postAttributes)
    {
        $postIds = ArrayToolkit::column($postAttributes, 'attributeId');
        if (empty($postIds)) {
            return array();
        }

        $users = $this->getUserService()->searchUsers(
            array('postIds' => $postIds, 'locked' => 0, 'noType' => 'system'),
            array('id' => 'DESC'),
            0,
            PHP_INT_MAX
        );
        if (empty($users)) {
            return array();
        }

        return ArrayToolkit::column($users, 'id');
    }

    protected function findUserIdsByUser($userAttributes)
    {
        $userIds = ArrayToolkit::column($userAttributes, 'attributeId');
        if (empty($userIds)) {
            return array();
        }

        $users = $this->getUserService()->searchUsers(
            array('userIds' => $userIds, 'locked' => 0, 'noType' => 'system'),
            array('id' => 'DESC'),
            0,
            PHP_INT_MAX
        );
        if (empty($users)) {
            return array();
        }

        return ArrayToolkit::column($users, 'id');
    }

    protected function findUserIdsByUserGroup($userGroupAttributes)
    {
        $userGroupIds = ArrayToolkit::column($userGroupAttributes, 'attributeId');
        if (empty($userGroupIds)) {
            return array();
        }

        $userIds = array();
        foreach ($userGroupIds as $userGroupId) {
            $userGroupIdUserIds = $this->getUserGroupMemberService()->findDistinctUserIdsByGroupId($userGroupId);
            $userIds = array_merge($userGroupIdUserIds, $userIds);
        }

        return $userIds;
    }

    protected function getSearchAttributeNameFuncName($attributeType)
    {
        return 'searchNameBy'.ucfirst($attributeType);
    }

    protected function searchNameByOrg($name, $orgIds)
    {
        $orgsName = array();
        $conditions = array();

        $conditions['name'] = $name;
        if (!empty($orgIds)) {
            $conditions['orgIds'] = $orgIds;
        }

        $orgs = $this->getOrgService()->searchOrgs(
            $conditions,
            array(),
            0,
            $this->searchLimit
        );

        if (empty($orgs)) {
            return $orgsName;
        }

        foreach ($orgs as $org) {
            $users = $this->getUserOrgService()->searchUserOrgs(array('orgId' => $org['id']), array(), 0, PHP_INT_MAX);
            if (empty($users)) {
                $num = 0;
            } else {
                $userIds = ArrayToolkit::column($users, 'userId');
                $num = $this->getUserService()->countUsers(
                    array('userIds' => $userIds, 'locked' => 0, 'noType' => 'system')
                );
            }
            $orgsName[] = array(
                'id' => $org['id'],
                'name' => $org['name'].'('.$num.')',
                'attributeType' => 'org',
            );
        }

        return $orgsName;
    }

    protected function searchNameByPost($name, $orgIds)
    {
        $postsName = array();

        $posts = $this->getPostService()->searchPosts(
            array('likeName' => $name),
            array(),
            0,
            $this->searchLimit
        );

        if (empty($posts)) {
            return $postsName;
        }

        $userIds = $this->findUserIdsByOrgIds($orgIds);

        foreach ($posts as $post) {
            $num = $this->getUserService()->countUsers(
                array('postId' => $post['id'], 'userIds' => $userIds, 'locked' => 0, 'noType' => 'system')
            );
            $postsName[] = array(
                'id' => $post['id'],
                'name' => $post['name'].'('.$num.')',
                'attributeType' => 'post',
            );
        }

        return $postsName;
    }

    protected function searchNameByUser($name, $orgIds)
    {
        $userNames = array();
        $conditions = array();
        $conditions['truename'] = $name;
        $conditions['locked'] = 0;
        $conditions['noType'] = 'system';
        if (!empty($orgIds)) {
            $conditions['orgIds'] = $orgIds;
        }

        $conditions['userIds'] = $this->findUserIdsByOrgIds($orgIds);

        unset($conditions['orgIds']);

        $users = $this->getUserService()->searchUsers(
            $conditions,
            array('id' => 'DESC'),
            0,
            $this->searchLimit
        );

        if (empty($users)) {
            return $userNames;
        }

        $userProfiles = $this->getUserService()->findUserProfilesByIds(ArrayToolkit::column($users, 'id'));
        $userProfiles = ArrayToolkit::index($userProfiles, 'id');

        foreach ($users as $user) {
            $userNames[] = array(
                'id' => $user['id'],
                'name' => $userProfiles[$user['id']]['truename'].'('.$user['nickname'].')',
                'attributeType' => 'user',
            );
        }

        return $userNames;
    }

    protected function searchNameByUserGroup($name, $orgIds)
    {
        $userGroupsName = array();

        $userGroups = $this->getUserGroupService()->searchUserGroups(
            array('likeName' => $name),
            array(),
            0,
            $this->searchLimit
        );

        if (empty($userGroups)) {
            return $userGroupsName;
        }

        foreach ($userGroups as $userGroup) {
            $num = $this->getUserGroupMemberService()->countUserGroupMemberByGroupIdAndOrgIds($userGroup['id'], $orgIds);
            $userGroupsName[] = array(
                'id' => $userGroup['id'],
                'name' => $userGroup['name'].'('.$num.')',
                'attributeType' => 'userGroup',
            );
        }

        return $userGroupsName;
    }

    protected function findUserIdsByOrgIds($orgIds)
    {
        $users = $this->getUserOrgService()->searchUserOrgs(
            array('orgIds' => $orgIds),
            array(),
            0,
            PHP_INT_MAX
        );
        $userIds = ArrayToolkit::column($users, 'userId');

        return array_unique($userIds);
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('CorporateTrainingBundle:User:UserService');
    }

    /**
     * @return UserOrgService
     */
    protected function getUserOrgService()
    {
        return $this->createService('CorporateTrainingBundle:User:UserOrgService');
    }

    /**
     * @return MemberService
     */
    protected function getUserGroupMemberService()
    {
        return $this->createService('CorporateTrainingBundle:UserGroup:MemberService');
    }

    /**
     * @return OrgService
     */
    protected function getOrgService()
    {
        return $this->createService('Org:OrgService');
    }

    /**
     * @return PostService
     */
    protected function getPostService()
    {
        return $this->createService('CorporateTrainingBundle:Post:PostService');
    }

    /**
     * @return UserGroupService
     */
    protected function getUserGroupService()
    {
        return $this->createService('CorporateTrainingBundle:UserGroup:UserGroupService');
    }
}
