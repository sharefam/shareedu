<?php

namespace CorporateTrainingBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\Admin\BaseController;
use AppBundle\Common\Paginator;
use Topxia\Service\Common\ServiceKernel;

class UserGroupController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = $request->query->all();
        $userCount = $this->getUserGroupService()->countUserGroup($conditions);
        $paginator = new Paginator(
            $this->get('request'),
            $userCount,
            20
        );

        $userGroups = $this->getUserGroupService()->searchUserGroups(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        foreach ($userGroups as &$userGroup) {
            $userGroup['num'] = $this->getUserGroupMemberService()->countUserGroupMemberByGroupId($userGroup['id']);
        }

        return $this->render('admin/user-group/index.html.twig',
            array(
            'userGroups' => $userGroups,
            'paginator' => $paginator,
            )
        );
    }

    public function createAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $userGroup = $request->request->all();
            $user = $this->getCurrentUser();
            $userGroup['createdUserId'] = $user['id'];
            $userGroup = $this->getUserGroupService()->createUserGroup($userGroup);

            return $this->redirect($this->generateUrl('admin_user_group_manage'));
        }

        return $this->render('admin/user-group/user-group-modal.html.twig');
    }

    public function editAction(Request $request, $id)
    {
        if ($request->getMethod() == 'POST') {
            $fields = $request->request->all();
            $userGroup = $this->getUserGroupService()->updateUserGroup($id, $fields);

            return $this->redirect($this->generateUrl('admin_user_group_manage'));
        }

        $userGroup = $this->getUserGroupService()->getUserGroup($id);

        return $this->render('admin/user-group/user-group-modal.html.twig',
            array(
                'userGroup' => $userGroup,
            )
        );
    }

    public function checkGroupNameAction(Request $request)
    {
        $name = $request->query->get('value');
        $exclude = $request->query->get('exclude');
        $available = $this->getUserGroupService()->isUserGroupNameAvailable($name, $exclude);

        if ($available) {
            $result = array(
                'success' => true,
                'message' => '',
            );
        } else {
            $result = array(
                'success' => false,
                'message' => '用户分组已存在',
            );
        }

        return $this->createJsonResponse($result);
    }

    public function checkGroupCodeAction(Request $request)
    {
        $code = $request->query->get('value');
        $exclude = $request->query->get('exclude');
        $isAvailable = $this->getUserGroupService()->isUserGroupCodeAvailable($code, $exclude);

        if ($isAvailable) {
            $response = array('success' => true, 'message' => '');
        } else {
            $response = array('success' => false, 'message' => ServiceKernel::instance()->trans('admin.user_group.message.check_group_code_error'));
        }

        return $this->createJsonResponse($response);
    }

    public function deleteAction(Request $request, $id)
    {
        $result = $this->getUserGroupService()->deleteUserGroup($id);
        if ($result) {
            $result = array('success' => $result, 'message' => ServiceKernel::instance()->trans('admin.user_group.message.delete_success'));
        } else {
            $result = array('success' => $result, 'message' => ServiceKernel::instance()->trans('admin.user_group.message.delete_error'));
        }

        return $this->createJsonResponse($result);
    }

    protected function getUserGroupService()
    {
        return $this->createService('CorporateTrainingBundle:UserGroup:UserGroupService');
    }

    protected function getUserGroupMemberService()
    {
        return $this->createService('CorporateTrainingBundle:UserGroup:MemberService');
    }
}
