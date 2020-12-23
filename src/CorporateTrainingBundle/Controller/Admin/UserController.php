<?php

namespace CorporateTrainingBundle\Controller\Admin;

use CorporateTrainingBundle\Common\OrgToolkit;
use CorporateTrainingBundle\Biz\UserGroup\Service\MemberService;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Controller\Admin\UserController as BaseController;
use Topxia\Service\Common\ServiceKernel;

class UserController extends BaseController
{
    public function indexAction(Request $request)
    {
        $fields = $request->query->all();
        if (isset($fields['hireDate_GTE'])) {
            $fields['hireDate_GTE'] = strtotime($fields['hireDate_GTE']);
        }

        if (isset($fields['hireDate_LTE'])) {
            $fields['hireDate_LTE'] = strtotime($fields['hireDate_LTE']);
        }

        if (!empty($fields['locked'])) {
            $fields['locked'] = ('locked' == $fields['locked']) ? 1 : 0;
        } else {
            unset($fields['locked']);
        }

        $conditions = array(
            'roles' => '',
            'keywordType' => '',
            'keyword' => '',
            'keywordUserType' => '',
            'noType' => 'system',
            'hireDate_GTE',
            'hireDate_LTE',
        );

        $conditions = array_merge($conditions, $fields);
        $conditions = $this->fillOrgCode($conditions);
        $userCount = $this->getUserService()->countUsers($conditions);
        $paginator = new Paginator(
            $this->get('request'),
            $userCount,
            20
        );

        $users = $this->getUserService()->searchUsers(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        if (isset($conditions['keywordType']) && 'verifiedMobile' == $conditions['keywordType'] && !empty($conditions['keyword'])) {
            $profilesCount = $this->getUserService()->searchUserProfileCount(array('mobile' => $conditions['keyword']));
            $userProfiles = $this->getUserService()->searchUserProfiles(
                array('mobile' => $conditions['keyword']),
                array('id' => 'DESC'),
                0,
                $profilesCount
            );
            $userIds = ArrayToolkit::column($userProfiles, 'id');

            if (!empty($userIds)) {
                unset($conditions['keywordType']);
                unset($conditions['keyword']);
                $conditions['userIds'] = array_merge(ArrayToolkit::column($users, 'id'), $userIds);
            }
            $userCount = $this->getUserService()->countUsers($conditions);
            $paginator = new Paginator(
                $this->get('request'),
                $userCount,
                20
            );

            $users = $this->getUserService()->searchUsers(
                $conditions,
                array('createdTime' => 'DESC'),
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
            );
        }

        $app = $this->getAppService()->findInstallApp('UserImporter');

        $showUserExport = false;

        if (!empty($app) && array_key_exists('version', $app)) {
            $showUserExport = version_compare($app['version'], '1.0.2', '>=');
        }

        $userIds = ArrayToolkit::column($users, 'id');
        $profiles = $this->getUserService()->findUserProfilesByIds($userIds);

        $allRoles = $this->getAllRoles();
        if (isset($allRoles['ROLE_ADMIN'])) {
            unset($allRoles['ROLE_ADMIN']);
        }
        $userCountInfo = $this->getUserService()->countUsersByLockedStatus();
        $userLockedCount = (!isset($conditions['locked']) || 1 == $conditions['locked']) ? $this->getUserService()->countUsers(array_merge($conditions, array('locked' => 1))) : 0;

        return $this->render('admin/user/index.html.twig', array(
            'users' => $users,
            'allRoles' => $allRoles,
            'userCount' => $userCount,
            'userLockedCount' => $userLockedCount,
            'userCountInfo' => $userCountInfo,
            'paginator' => $paginator,
            'profiles' => $profiles,
            'showUserExport' => $showUserExport,
            'maxUsersNumber' => $this->getUserService()->getMaxUsersNumber(),
        ));
    }

    public function createAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $formData = $request->request->all();
            $formData['type'] = 'import';
            $formData['orgCodes'] = explode(',', $formData['orgCodes']);
            $formData['orgCodes'] = array_filter($formData['orgCodes']);
            if (empty($formData['orgCodes'])) {
                throw $this->createNotFoundException('The org cannot be empty!');
            }
            $registration = $this->getRegisterData($formData, $request->getClientIp());
            try {
                $user = $this->getAuthService()->register($registration);
            } catch (\Exception $e) {
                return $this->createJsonResponse(array('error' => $e->getMessage()));
            }
            $this->get('session')->set('registed_email', $user['email']);

            if (isset($formData['roles'])) {
                $roles = $formData['roles'];
                $roles[] = 'ROLE_USER';
                if (in_array('ROLE_TRAINING_ADMIN', $roles) && !in_array('ROLE_TEACHER', $roles)) {
                    $roles[] = 'ROLE_TEACHER';
                }

                $this->getUserService()->changeUserRoles($user['id'], $roles);
            }
            if (isset($formData['permissionOrgIds']) && !empty($formData['permissionOrgIds'])) {
                $orgIds = explode(',', $formData['permissionOrgIds']);
                if (!$this->getManagePermissionService()->checkOrgManagePermission($orgIds, $this->getCurrentUser()->getManageOrgIds())) {
                    return $this->createJsonResponse(array('status' => 'false', 'message' => $this->trans('admin.manage.org_permission_beyond_error')));
                }
                $this->getManagePermissionOrgService()->setUserManagePermissionOrgs($user['id'], $orgIds);
            }

            if (!empty($formData['postId'])) {
                $this->getUserService()->changeUserPost($user['id'], $formData['postId']);
            }

            if (!empty($formData['truename'])) {
                $this->getUserService()->updateUserProfile($user['id'], array('truename' => $formData['truename']));
            }

            if (!empty($formData['hireDate'])) {
                $this->getUserService()->updateUserHireDate($user['id'], strtotime($formData['hireDate']));
            }

            $this->getLogService()->info('user', 'add', "管理员添加新用户 {$user['nickname']} ({$user['id']})");

            return $this->redirect($this->generateUrl('admin_user'));
        }
        $user = $this->getCurrentUser();

        return $this->render('admin/user/create-modal.html.twig', array('user' => $user));
    }

    public function editAction(Request $request, $id)
    {
        $user = $this->getUserService()->getUser($id);

        $profile = $this->getUserService()->getUserProfile($user['id']);
        $profile['title'] = $user['title'];

        if ('POST' == $request->getMethod()) {
            $profile = $request->request->all();
            if (!empty($profile['hireDate'])) {
                $hireDate = strtotime($profile['hireDate']);
                if ($user['hireDate'] !== $hireDate) {
                    $user = $this->getUserService()->updateUserHireDate($id, $hireDate);
                }
            }

            if (!((strlen($user['verifiedMobile']) > 0) && isset($profile['mobile']))) {
                $profile = $this->getUserService()->updateUserProfile($user['id'], $profile);
                $this->getLogService()->info('user', 'edit', "管理员编辑用户资料 {$user['nickname']} (#{$user['id']})", $profile);
            } else {
                $this->setFlashMessage('danger', 'admin.user.message.edit_error');
            }

            return $this->render('admin/user/user-table-tr.html.twig', array(
                'user' => $user,
                'profile' => $this->getUserService()->getUserProfile($id),
            ));
        }

        $fields = $this->getFields();

        return $this->render('admin/user/edit-modal.html.twig', array(
            'user' => $user,
            'profile' => $profile,
            'fields' => $fields,
        ));
    }

    public function showAction(Request $request, $id)
    {
        $user = $this->getUserService()->getUser($id);
        $profile = $this->getUserService()->getUserProfile($id);
        $userGroups = $this->getUserGroupMemberService()->findUserGroupsByUserId($id);
        $profile['title'] = $user['title'];
        $fields = $this->getFields();
        $orgs = $this->getOrgService()->findOrgsByIds($user['orgIds']);
        $orgNames = OrgToolkit::buildOrgsNames($user['orgIds'], $orgs);

        return $this->render('admin/user/show-modal.html.twig', array(
            'user' => $user,
            'profile' => $profile,
            'fields' => $fields,
            'orgNames' => $orgNames,
            'userGroups' => $userGroups,
        ));
    }

    public function permissionsAction(Request $request, $id)
    {
        $user = $this->getUserService()->getUser($id);
        $currentUser = $this->getCurrentUser();
        $currentUserProfile = $this->getUserService()->getUserProfile($currentUser['id']);
        $user['manageOrgIds'] = $this->getManagePermissionService()->findUserManageOrgIdsByUserId($user['id']);

        if ('POST' == $request->getMethod()) {
            $data = $request->request->all();
            $roles = $data['roles'];
            if (in_array('ROLE_TRAINING_ADMIN', $roles) && !in_array('ROLE_TEACHER', $roles)) {
                $roles[] = 'ROLE_TEACHER';
            }
            $this->getUserService()->changeUserRoles($user['id'], $roles);

            $orgIds = $request->request->get('orgIds');
            if (isset($data['orgIds'])) {
                $orgIds = explode(',', $orgIds);
                if (!$this->getManagePermissionService()->checkOrgManagePermission($orgIds, $user['manageOrgIds'])) {
                    return $this->createJsonResponse(array('status' => 'false', 'message' => $this->trans('admin.manage.org_permission_beyond_error')));
                }
                $this->getManagePermissionOrgService()->setUserManagePermissionOrgs($id, $orgIds);
            }

            if (!empty($roles)) {
                $roleSet = $this->getRoleService()->searchRoles(array(), 'created', 0, 9999);
                $rolesByIndexCode = ArrayToolkit::index($roleSet, 'code');
                $roleNames = $this->getRoleNames($roles, $rolesByIndexCode);

                $message = array(
                    'userId' => $currentUser['id'],
                    'userName' => !empty($currentUserProfile['truename']) ? $currentUserProfile['truename'] : $currentUser['nickname'],
                    'role' => implode(',', $roleNames),
                );

                $this->getNotifiactionService()->notify($user['id'], 'role', $message);
            }
            $user = $this->getUserService()->getUser($id);

            return $this->render('admin/user/user-table-tr.html.twig', array(
                'user' => $user,
                'profile' => $this->getUserService()->getUserProfile($id),
            ));
        }

        return $this->render('admin/user/permissions-modal.html.twig', array(
            'user' => $user,
            'orgIds' => implode(',', $this->buildUserPermissionSelectOrgIds($id)),
        ));
    }

    protected function buildUserPermissionSelectOrgIds($id)
    {
        $userOrgCodes = $this->getManagePermissionOrgService()->findUserManageOrgCodesByUserId($id);
        if (empty($userOrgCodes)) {
            return array();
        }
        $orgIds = $this->getOrgService()->findOrgsByPrefixOrgCodes($userOrgCodes, array('id'));

        return ArrayToolkit::column($orgIds, 'id');
    }

    public function changePostAction(Request $request, $id)
    {
        $user = $this->getUserService()->getUser($id);

        if ('POST' == $request->getMethod()) {
            $postId = $request->request->get('postId');
            if (empty($postId)) {
                return $this->createJsonResponse(array('message' => ServiceKernel::instance()->trans('admin.user.message.change_post_empty')));
            }

            $this->getUserService()->changeUserPost($user['id'], $postId);

            return $this->render('admin/user/user-table-tr.html.twig', array(
                'user' => $this->getUserService()->getUser($id),
                'profile' => $this->getUserService()->getUserProfile($id),
            ));
        }

        return $this->render('admin/user/change-post-modal.html.twig', array(
            'user' => $user,
        ));
    }

    public function changeNicknameAction(Request $request, $id)
    {
        $user = $this->getUserService()->getUser($id);

        if ('POST' == $request->getMethod()) {
            $nickname = $request->request->get('newNickname');

            $this->getUserService()->changeNickname($user['id'], $nickname);

            return $this->render('admin/user/user-table-tr.html.twig', array(
                'user' => $this->getUserService()->getUser($id),
                'profile' => $this->getUserService()->getUserProfile($id),
            ));
        }

        return $this->render('admin/user/change-nickname-modal.html.twig', array(
            'user' => $user,
        ));
    }

    public function batchUpdatePostAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $ids = $request->request->get('ids');
            $postId = $request->request->get('postId');
            if (empty($postId)) {
                return $this->createJsonResponse(array('message' => ServiceKernel::instance()->trans('admin.user.message.change_post_empty')));
            }
            $this->getUserService()->batchUpdatePost($ids, $postId);

            return $this->createJsonResponse(true);
        }

        return $this->render('admin/user/batch-update-post-modal.html.twig');
    }

    public function batchUpdateOrgsAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $ids = $request->request->get('ids');
            $orgCodes = $request->request->get('orgCodes');
            $orgCodes = explode(',', $orgCodes);

            $this->getUserService()->batchUpdateOrgs($ids, $orgCodes);

            return $this->createJsonResponse(true);
        }

        return $this->render('org/batch-update-orgs-modal.html.twig');
    }

    public function batchLockUserAction(Request $request)
    {
        $ids = $request->request->get('ids');

        $this->getUserService()->batchLockUser($ids);

        return $this->createJsonResponse(true);
    }

    public function lockAction($id)
    {
        $this->getUserService()->lockUser($id);
        $this->kickUserLogout($id);

        return $this->render('admin/user/user-table-tr.html.twig', array(
            'user' => $this->getUserService()->getUser($id),
            'profile' => $this->getUserService()->getUserProfile($id),
        ));
    }

    public function unlockAction($id)
    {
        try {
            $this->getUserService()->unlockUser($id);

            return $this->render('admin/user/user-table-tr.html.twig', array(
                'user' => $this->getUserService()->getUser($id),
                'profile' => $this->getUserService()->getUserProfile($id),
            ));
        } catch (\Exception $e) {
            return $this->createJsonResponse(array('error' => $e->getMessage()));
        }
    }

    public function orgUpdateAction(Request $request, $id)
    {
        $user = $this->getUserService()->getUser($id);

        if ($request->isMethod('POST')) {
            $orgCodes = $request->request->get('orgCodes', $user['orgCodes']);

            if (!is_array($orgCodes)) {
                $orgCodes = explode(',', $orgCodes);
            }

            $this->getUserService()->changeUserOrgs($user['id'], $orgCodes);
        }

        $orgs = $this->getOrgService()->findOrgsByIds($user['orgIds']);

        return $this->render('admin/user/update-org-modal.html.twig', array(
            'user' => $user,
            'orgCodes' => ArrayToolkit::column($orgs, 'orgCode'),
        ));
    }

    protected function getRegisterData($formData, $clientIp)
    {
        $userData = parent::getRegisterData($formData, $clientIp);
        if (isset($formData['postId'])) {
            $userData['postId'] = $formData['postId'];
        }

        if (isset($formData['orgCodes'])) {
            $formData['orgCodes'] = implode('|', $formData['orgCodes']);
            $userData['orgCodes'] = $formData['orgCodes'];
        }

        if (!empty($formData['hireDate'])) {
            $userData['hireDate'] = strtotime($formData['hireDate']);
        }

        return $userData;
    }

    protected function getUserService()
    {
        return $this->createService('CorporateTrainingBundle:User:UserService');
    }

    protected function getUserOrgService()
    {
        return $this->createService('CorporateTrainingBundle:User:UserOrgService');
    }

    protected function getPostService()
    {
        return $this->createService('CorporateTrainingBundle:Post:PostService');
    }

    /**
     * @return MemberService
     */
    protected function getUserGroupMemberService()
    {
        return $this->createService('CorporateTrainingBundle:UserGroup:MemberService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ManagePermission\Service\ManagePermissionOrgService
     */
    protected function getManagePermissionOrgService()
    {
        return $this->createService('CorporateTrainingBundle:ManagePermission:ManagePermissionOrgService');
    }
}
