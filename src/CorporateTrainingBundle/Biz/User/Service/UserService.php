<?php

namespace CorporateTrainingBundle\Biz\User\Service;

interface UserService
{
    public function searchUsers(array $conditions, array $orderBy, $start, $limit, $columns = array());

    public function initPassword($id, $newPassword);

    public function changeUserPost($id, $postId);

    public function batchUpdatePost($ids, $postId);

    public function readGuide($id);

    public function unlockUser($id);

    public function countUsersByLockedStatus();

    public function isOverMaxUsersNumber();

    public function getMaxUsersNumber();

    public function statisticsOrgUserNumGroupByOrgId();

    public function statisticsPostUserNumGroupByPostId();

    public function batchUpdateOrgs($userIds, $orgCodes);

    public function batchLockUser($userIds);

    public function initOrgsRelation();

    public function changePwdInit($id);

    public function updateUserHireDate($userId, $hireDate);

    public function findFollowersByFromId($fromId);

    public function sortPromoteUser($ids);

    /**
     * 获取数据中心自定义查询字段
     *
     * @param [integer] $id 用户ID
     *
     * @return array 自定义字段
     */
    public function getUserCustomColumns($id);

    /**
     * 更新数据中心自定义查询字段
     *
     * @param [integer] $id      用户ID
     * @param [Array]   $columns 自定义字段
     *
     * @return array 更新成功返回自定义字段
     */
    public function updateUserCustomColumns($id, $columns);

    public function getDingTalkUsers($userIds);

    public function updateUserBind($id, $fields);

    public function searchUserBinds($conditions, $orderBys, $start, $limit, $columns = array());

    public function countUserBinds($conditions);
}
