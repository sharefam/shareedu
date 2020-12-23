<?php

namespace CorporateTrainingBundle\Controller\Admin;

use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\Admin\OrgManageController as BaseController;
use Topxia\Service\Common\ServiceKernel;

class OrgManageController extends BaseController
{
    public function indexAction(Request $request)
    {
        /**
         * 用户多组织机构改造，如果没有传orgId取用户orgIds第一位
         */
        $currentUser = $this->getCurrentUser();
        $manageOrgIds = $this->getManagePermissionService()->findUserManageOrgIdsByUserId($currentUser['id']);
        $manageOrgs = $this->getOrgService()->findOrgsByIds($manageOrgIds);

        $pickedOrgId = $request->query->get('orgId', '');
        $orgId = empty($pickedOrgId) || !in_array($pickedOrgId, $manageOrgIds) ? $manageOrgIds[0] : $pickedOrgId;
        $org = $this->getOrgService()->getOrg($orgId);
        $user = array();
        if (!empty($org['createdUserId'])) {
            $user = $this->getUserService()->getUser($org['createdUserId']);
        }
        $syncSetting = $this->getSettingService()->get('sync_department_setting', array());

        return $this->render(
            'admin/org-manage/index.html.twig',
            array(
                'manageOrgs' => $manageOrgs,
                'org' => $org,
                'user' => $user,
                'syncSetting' => $syncSetting,
            )
        );
    }

    public function findOrgChildrensAction(Request $request, $id)
    {
        $orgs = $this->getOrgService()->findOrgsByParentId($id);
        $userIds = ArrayToolkit::column($orgs, 'createdUserId');
        $users = $this->getUserService()->findUsersByIds($userIds);
        $users = ArrayToolkit::index($users, 'id');

        return $this->render(
            'admin/org-manage/org-items.html.twig',
            array(
                'rootOrgId' => $id,
                'orgs' => $orgs,
                'createdUsers' => $users,
            )
        );
    }

    public function findOrgAction(Request $request, $id)
    {
        $org = $this->getOrgService()->getOrg($id);
        $user = $this->getUserService()->getUser($org['createdUserId']);

        return $this->render(
            'admin/org-manage/org-item.html.twig',
            array(
                'org' => $org,
                'user' => $user,
                'hasChildren' => $org['childrenNum'] > 0,
            )
        );
    }

    public function batchCreateAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $orgNames = $request->request->get('orgNames');
            $parentId = $request->request->get('parentId');
            $orgNames = trim($orgNames);
            $orgNames = explode("\r\n", $orgNames);
            $orgNames = array_filter($orgNames);

            $this->getOrgService()->batchCreateOrg($orgNames, $parentId);

            return $this->createJsonResponse(array('success' => true, 'parentId' => $parentId));
        }

        $parentId = $request->query->get('parentId', 0);
        $parentOrg = $this->getOrgService()->getOrg($parentId);

        return $this->render('admin/org-manage/batch-create-modal.html.twig', array('parentOrg' => $parentOrg));
    }

    public function batchCreateCheckNameAction(Request $request)
    {
        $parentId = $request->query->get('parentId');
        $orgNames = $request->query->get('value');
        $orgNames = trim($orgNames);
        $orgNames = explode("\n", $orgNames);
        $orgNames = array_filter($orgNames);
        $existNames = '';
        foreach ($orgNames as $orgName) {
            $isAvaliable = $this->getOrgService()->isNameAvaliable($orgName, $parentId, null);

            if (!$isAvaliable) {
                $existNames .= $orgName.',';
            }
        }

        if (empty($existNames)) {
            $response = array('success' => true, 'message' => '');
        } else {
            $response = array('success' => false, 'message' => ServiceKernel::instance()->trans('admin.org_manage.message.batch_create_check_name', array('%existNames%' => $existNames)));
        }

        return $this->createJsonResponse($response);
    }

    public function syncDepartmentAction(Request $request)
    {
        return $this->render('admin/org-manage/sync-department-modal.html.twig');
    }

    public function syncDepartmentFromAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $result = $this->getOrgSyncService()->syncFrom();

            return $this->createJsonResponse($this->buildSyncDepartmentResult($result));
        }
    }

    public function syncDepartmentToAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $result = $this->getOrgSyncService()->syncTo();

            return $this->createJsonResponse($this->buildSyncDepartmentResult($result));
        }
    }

    protected function buildSyncDepartmentResult($result)
    {
        if ($result['success']) {
            $result = array('success' => $result['success'], 'type' => 'success', 'message' => ServiceKernel::instance()->trans('admin.org_manage.sync_department.message.sync_success'));
        } else {
            if (2 == $result['code']) {
                $type = 'warning';
                $message = ServiceKernel::instance()->trans('admin.org_manage.sync_department.message.sync_error_code_2');
            } elseif (3 == $result['code']) {
                $type = 'warning';
                $message = ServiceKernel::instance()->trans('admin.org_manage.sync_department_to.message.sync_error_code_3');
            } else {
                $type = 'danger';
                $message = ServiceKernel::instance()->trans('admin.org_manage.sync_department.message.sync_error');
            }
            $result = array('success' => $result['success'], 'type' => $type, 'message' => $message);
        }

        return $result;
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function getOrgSyncService()
    {
        return $this->createService('OrgSync:OrgSyncService');
    }
}
