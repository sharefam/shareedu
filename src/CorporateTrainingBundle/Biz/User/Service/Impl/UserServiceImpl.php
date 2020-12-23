<?php

namespace CorporateTrainingBundle\Biz\User\Service\Impl;

use Codeages\Biz\Framework\Util\ArrayToolkit;
use CorporateTrainingBundle\Biz\CloudPlatform\Service\EduCloudService;
use CorporateTrainingBundle\Biz\User\Service\UserService;
use Biz\User\Service\Impl\UserServiceImpl as BaseServiceImpl;
use Codeages\Biz\Framework\Event\Event;
use Biz\System\Service\SessionService;
use Biz\User\Service\TokenService;
use CorporateTrainingBundle\Common\Constant\CTConst;
use Topxia\Service\Common\ServiceKernel;

class UserServiceImpl extends BaseServiceImpl implements UserService
{
    public function getUserWithOrgScopes($id)
    {
        $user = $this->getUserDao()->get($id);
        if (!($user)) {
            return null;
        }

        $user = UserSerialize::unserialize($user);
        $user['lineOrgIds'] = $this->getUserlineOrgIds($user);
        $user['manageOrgIds'] = $this->getUserManageOrgIds($user);
        $user['manageOrgCodes'] = $this->getUserManageOrgCodes($user);

        return $user;
    }

    public function searchUsers(array $conditions, array $orderBy, $start, $limit, $columns = array())
    {
        if (isset($conditions['nickname'])) {
            $conditions['nickname'] = strtoupper($conditions['nickname']);
        }

        $users = $this->getUserDao()->searchUsers($conditions, $orderBy, $start, $limit, $columns);

        return UserSerialize::unserializes($users);
    }

    public function countUsers(array $conditions)
    {
        if (isset($conditions['nickname'])) {
            $conditions['nickname'] = strtoupper($conditions['nickname']);
        }

        return $this->getUserDao()->countUsers($conditions);
    }

    public function unlockUser($id)
    {
        if ($this->isOverMaxUsersNumber()) {
            $maxNumber = $this->getSettingService()->get('max_users_number', 0);
            throw $this->createServiceException(ServiceKernel::instance()->trans('admin.user.auth.message.register_exception', array('%max_users_number%' => $maxNumber)));
        }
        parent::unlockUser($id);
    }

    public function initPassword($id, $newPassword)
    {
        $connection = $this->getKernel()->getConnection();
        $connection->beginTransaction();

        try {
            $init = array(
                'pwdInit' => '1',
            );

            $this->getAuthService()->changePassword($id, null, $newPassword);
            $this->getUserDao()->update($id, $init);

            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollback();
            throw $e;
        }

        return $this->getUserDao()->update($id, $init);
    }

    public function changePwdInit($id)
    {
        $init = array(
            'pwdInit' => '1',
        );

        return $this->getUserDao()->update($id, $init);
    }

    public function readGuide($id)
    {
        $user = $this->getUser($id);

        if (empty($user)) {
            throw $this->createNotFoundException('User', $id, '用户不存在');
        }

        return $this->getUserDao()->update($id, array('readGuide' => 1));
    }

    public function changeUserPost($id, $postId)
    {
        $user = $this->getUser($id);

        if (empty($user)) {
            throw $this->createNotFoundException('User', $id, '用户不存在');
        }

        $post = $this->getPostService()->getPost($postId);

        if (empty($post)) {
            throw $this->createNotFoundException('Post', $id, '岗位不存在');
        }

        $updatedUser = $this->getUserDao()->update($id, array('postId' => $postId));
        $this->dispatchEvent('user.post.update', new Event($updatedUser));
        $this->dispatchEvent('user.change_post', new Event($updatedUser));
        $this->getLogService()->info('user', 'post_change', "修改用户{$user['nickname']}岗位为{$post['name']}成功");

        return $updatedUser;
    }

    public function findUserProfilesByTrueName($truename)
    {
        if (empty($truename)) {
            return null;
        }

        return $this->getUserProfileDao()->findByTrueName($truename);
    }

    public function statisticsOrgUserNumGroupByOrgId()
    {
        return $this->getUserDao()->statisticsOrgUserNumGroupByOrgId();
    }

    public function statisticsPostUserNumGroupByPostId()
    {
        return $this->getUserDao()->statisticsPostUserNumGroupByPostId();
    }

    public function batchUpdatePost($ids, $postId)
    {
        $ids = explode(',', $ids);
        $connection = $this->getKernel()->getConnection();
        $connection->beginTransaction();

        try {
            foreach ($ids as $id) {
                $this->changeUserPost($id, $postId);
            }
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollback();
            throw $e;
        }
    }

    public function countUsersByLockedStatus()
    {
        return array(
            'enable' => $this->countUsers(array('locked' => 0, 'noType' => 'system')),
            'disable' => $this->countUsers(array('locked' => 1)),
        );
    }

    public function isOverMaxUsersNumber()
    {
        $counts = $this->countUsersByLockedStatus();
        $maxUsersNumber = $this->getMaxUsersNumber();

        return ($maxUsersNumber > 0) && ($counts['enable'] >= $maxUsersNumber);
    }

    public function getMaxUsersNumber()
    {
        return $this->getSettingService()->get('max_users_number', 0);
    }

    public function changeUserOrgs($userId, $orgCodes)
    {
        $user = $this->getUser($userId);
        if (empty($user) || ($user['orgCodes'] == $orgCodes)) {
            return array();
        }

        $orgCodes = $this->filterOrgCodes($orgCodes);
        $orgs = $this->getOrgService()->findOrgsByOrgCodes($orgCodes);
        $existOrgCodes = ArrayToolkit::column($orgs, 'orgCode');

        if (array_diff($orgCodes, $existOrgCodes)) {
            throw $this->createNotFoundException('Org Not Found');
        }
        $this->getKernel()->getConnection()->beginTransaction();
        try {
            $fields = array(
                'orgCodes' => empty($orgCodes) ? array(1.) : ArrayToolkit::column($orgs, 'orgCode'),
                'orgIds' => empty($orgCodes) ? array(1) : ArrayToolkit::column($orgs, 'id'),
            );

            $this->getUserDao()->update($userId, $fields);
            $this->getUserOrgService()->setUserOrgs($userId, $orgs);
            $this->getKernel()->getConnection()->commit();
        } catch (\Exception $e) {
            $this->getKernel()->getConnection()->rollBack();
            throw $e;
        }

        return $user;
    }

    public function getUserBindByTypeAndFromId($type, $fromId)
    {
        if ('weixinweb' == $type || 'weixinmob' == $type) {
            $type = 'weixin';
        }

        if ('dingtalkweb' == $type || 'dingtalkmob' == $type) {
            $type = 'dingtalk';
        }

        return $this->getUserBindDao()->getByTypeAndFromId($type, $fromId);
    }

    public function getUserBindByTypeAndUserId($type, $toId)
    {
        $user = $this->getUserDao()->get($toId);

        if (empty($user)) {
            throw $this->createNotFoundException("User#{$toId} Not Found");
        }

        if (!$this->typeInOAuthClient($type)) {
            throw $this->createInvalidArgumentException('Invalid Type');
        }

        if ('weixinweb' == $type || 'weixinmob' == $type) {
            $type = 'weixin';
        }

        if ('dingtalkweb' == $type || 'dingtalkmob' == $type) {
            $type = 'dingtalk';
        }

        return $this->getUserBindDao()->getByToIdAndType($type, $toId);
    }

    public function bindUser($type, $fromId, $toId, $token)
    {
        $user = $this->getUserDao()->get($toId);

        if (empty($user)) {
            throw $this->createNotFoundException("User#{$toId} Not Found");
        }

        if (!$this->typeInOAuthClient($type)) {
            throw $this->createInvalidArgumentException('Invalid Type');
        }

        if ('weixinweb' == $type || 'weixinmob' == $type) {
            $type = 'weixin';
        }

        if ('dingtalkweb' == $type || 'dingtalkmob' == $type) {
            $type = 'dingtalk';
        }

        if ($this->isUserBound($toId, $type)) {
            throw $this->createAccessDeniedException('account already bound');
        }

        $bind = $this->getUserBindDao()->create(array(
            'type' => $type,
            'fromId' => $fromId,
            'toId' => $toId,
            'token' => empty($token['token']) ? '' : $token['token'],
            'createdTime' => time(),
            'expiredTime' => empty($token['expiredTime']) ? 0 : $token['expiredTime'],
        ));

        $this->dispatchEvent('user.bind', new Event($bind));
    }

    public function isUserBound($userId, $type)
    {
        if ('weixinweb' == $type || 'weixinmob' == $type) {
            $type = 'weixin';
        }

        if ('dingtalkweb' == $type || 'dingtalkmob' == $type) {
            $type = 'dingtalk';
        }

        $bind = $this->getUserBindDao()->getByToIdAndType($type, $userId);

        return !empty($bind) ? true : false;
    }

    public function batchUpdateOrgs($userIds, $orgCodes)
    {
        $currentUser = $this->getCurrentUser();
        $userIds = $this->filterUserIds($userIds);
        foreach ($userIds as $userId) {
            if ($currentUser['id'] == $userId) {
                continue;
            }

            $this->changeUserOrgs($userId, $orgCodes);
        }
    }

    public function batchLockUser($userIds)
    {
        $currentUser = $this->getCurrentUser();

        foreach ($userIds as $userId) {
            $user = $this->getUser($userId);
            if ($currentUser['id'] == $userId || $user['locked']) {
                continue;
            }
            $this->lockUser($userId);
            $this->kickUserLogout($userId);
        }
    }

    public function findUserIdsByNickNameOrTrueName($name)
    {
        $userIdsByNickname = $this->getUserDao()->findUserIds(array('nickname' => $name));
        $userIdsByNickname = ArrayToolkit::column($userIdsByNickname, 'id');
        $userIdsByTruename = $this->getProfileDao()->findUserIds(array('truename' => $name));
        $userIdsByTruename = ArrayToolkit::column($userIdsByTruename, 'id');

        return  array_merge($userIdsByNickname, $userIdsByTruename);
    }

    protected function kickUserLogout($userId)
    {
        $this->getSessionService()->clearByUserId($userId);
        $tokens = $this->getTokenService()->findTokensByUserIdAndType($userId, 'mobile_login');
        if (!empty($tokens)) {
            foreach ($tokens as $token) {
                $this->getTokenService()->destoryToken($token['token']);
            }
        }
    }

    protected function filterOrgCodes($orgCodes)
    {
        if (is_array($orgCodes)) {
            return $orgCodes;
        } else {
            return explode('|', $orgCodes);
        }
    }

    protected function filterUserIds($userIds)
    {
        if (is_array($userIds)) {
            return $userIds;
        } else {
            return explode(',', $userIds);
        }
    }

    public function initOrgsRelation()
    {
        $fields = array('|1|', '|1.|');

        $org = $this->getOrgService()->getOrgByOrgCode(CTConst::ROOT_ORG_CODE);
        $users = $this->searchUsers(array(), array(), 0, PHP_INT_MAX);
        if (!empty($users)) {
            foreach ($users as $user) {
                $this->getUserOrgService()->setUserOrgs($user['id'], array($org));
            }
        }

        return $this->getUserDao()->initOrgsRelation($fields);
    }

    public function initSystemUsers()
    {
        $users = array(
            array(
                'type' => 'system',
                'roles' => array('ROLE_USER', 'ROLE_SUPER_ADMIN'),
            ),
        );
        foreach ($users as $user) {
            $existsUser = $this->getUserDao()->getUserByType($user['type']);

            if (!empty($existsUser)) {
                continue;
            }

            $user['nickname'] = $this->generateNickname($user).'(系统用户)';
            $user['emailVerified'] = 1;
            $user['password'] = $this->getRandomChar();
            $user['email'] = $this->generateEmail($user);
            $user['salt'] = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
            $user['password'] = $this->getPasswordEncoder()->encodePassword($user['password'], $user['salt']);
            $user = UserSerialize::unserialize(
                $this->getUserDao()->create(UserSerialize::serialize($user))
            );

            $profile = array();
            $profile['id'] = $user['id'];
            $this->getProfileDao()->create($profile);
        }
    }

    public function updateUserHireDate($userId, $hireDate)
    {
        return $this->getUserDao()->update($userId, array('hireDate' => $hireDate));
    }

    public function findFollowersByFromId($fromId)
    {
        return $this->getFriendDao()->findFollowingsByFromId($fromId);
    }

    public function sortPromoteUser($ids)
    {
        if (!empty($ids)) {
            foreach ($ids as $index => $id) {
                $this->promoteUser($id, $index + 1);
            }
        }
    }

    public function getUserCustomColumns($id)
    {
        $profile = $this->getUserProfile($id);
        $customColumns = $profile['custom_column'];
        $defaultColumns = array(
            'post',
            'org',
            'online_course_learn',
            'project_plan',
            'online_study_hours',
            'offline_study_hours',
            'subject_exam',
        );

        return !empty($customColumns) ? $customColumns : $defaultColumns;
    }

    public function updateUserCustomColumns($id, $columns)
    {
        $this->updateUserProfile($id, array('custom_column' => $columns));

        return $this->getUserCustomColumns($id);
    }

    public function updateUserBind($id, $fields)
    {
        return $this->getUserBindDao()->update($id, $fields);
    }

    public function getDingTalkUsers($userIds)
    {
        return $this->getUserBindDao()->search(array('type' => 'dingtalk', 'toIds' => $userIds), array(), 0, PHP_INT_MAX);
    }

    public function searchUserBinds($conditions, $orderBys, $start, $limit, $columns = array())
    {
        return $this->getUserBindDao()->search($conditions, array(), 0, PHP_INT_MAX);
    }

    public function countUserBinds($conditions)
    {
        return $this->getUserBindDao()->count($conditions);
    }

    protected function convertOAuthType($type)
    {
        if ('weixinweb' == $type || 'weixinmob' == $type) {
            $type = 'weixin';
        }

        if ('dingtalkweb' == $type || 'dingtalkmob' == $type) {
            $type = 'dingtalk';
        }

        return $type;
    }

    /*
     * 返回用户直属部门及直线向上所有父级部门id
     */
    protected function getUserLineOrgIds($user)
    {
        $orgIds = array();
        foreach ($user['orgCodes'] as $orgCode) {
            $orgIds = array_merge($orgIds, explode('.', substr($orgCode, 0, -1)));
        }

        return array_unique($orgIds);
    }

    /*
     * 返回用户管理范围组织机构id
     */
    protected function getUserManageOrgIds($user)
    {
        $manageOrgIds = $this->getManagePermissionService()->findUserManageOrgIdsByUserId($user['id']);

        return $manageOrgIds;
    }

    /*
     * 返回用户管理范围组织机构code
     */
    protected function getUserManageOrgCodes($user)
    {
        $manageOrgCodes = $this->getManagePermissionService()->findUserManageOrgCodesByUserId($user['id']);

        return $manageOrgCodes;
    }

    protected function getManagePermissionService()
    {
        return $this->createService('ManagePermission:ManagePermissionOrgService');
    }

    protected function getAuthService()
    {
        return $this->createService('User:AuthService');
    }

    protected function getPostService()
    {
        return $this->createService('CorporateTrainingBundle:Post:PostService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\User\Service\UserOrgService
     */
    protected function getUserOrgService()
    {
        return $this->createService('User:UserOrgService');
    }

    /**
     * @return SessionService
     */
    protected function getSessionService()
    {
        return $this->createService('System:SessionService');
    }

    /**
     * @return TokenService
     */
    protected function getTokenService()
    {
        return $this->createService('User:TokenService');
    }

    private function getUserProfileDao()
    {
        return $this->createDao('User:UserProfileDao');
    }

    /**
     * @return EduCloudService
     */
    protected function getEduCloudService()
    {
        return $this->createService('CorporateTrainingBundle:CloudPlatform:EduCloudService');
    }
}

class UserSerialize
{
    public static function serialize(array $user)
    {
        return $user;
    }

    public static function unserialize(array $user = null)
    {
        if (empty($user)) {
            return;
        }

        $user = self::_userRolesSort($user);

        return $user;
    }

    public static function unserializes(array $users)
    {
        return array_map(
            function ($user) {
                return UserSerialize::unserialize($user);
            },
            $users
        );
    }

    private static function _userRolesSort($user)
    {
        if (!empty($user['roles'][1]) && 'ROLE_USER' == $user['roles'][1]) {
            $temp = $user['roles'][1];
            $user['roles'][1] = $user['roles'][0];
            $user['roles'][0] = $temp;
        }

        //交换学员角色跟roles数组第0个的位置;

        return $user;
    }
}
