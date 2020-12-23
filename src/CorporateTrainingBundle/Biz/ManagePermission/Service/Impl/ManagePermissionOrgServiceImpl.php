<?php

namespace CorporateTrainingBundle\Biz\ManagePermission\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\Org\Service\OrgService;
use CorporateTrainingBundle\Biz\ManagePermission\Dao\ManagePermissionOrgDao;
use CorporateTrainingBundle\Biz\ManagePermission\Service\ManagePermissionOrgService;
use Biz\BaseService;
use CorporateTrainingBundle\Biz\ResourceUsePermissionShared\Service\ResourceUsePermissionSharedService;

class ManagePermissionOrgServiceImpl extends BaseService implements ManagePermissionOrgService
{
    public function createOrgRecord($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('userId', 'orgId'))) {
            throw $this->createServiceException('parameter is invalid!');
        }
        $fields['createdUserId'] = $this->getCurrentUser()->getId();
        $fields = $this->filterFields($fields);

        return $this->getManagePermissionOrgDao()->create($fields);
    }

    public function updateOrgRecord($id, $fields)
    {
        return $this->getManagePermissionOrgDao()->update($id, $fields);
    }

    /**
     * 判断设置部门是否超出管理员的管理范围
     *
     * @param $newSettingOrgIds   '新设置的部门Ids'
     * @param $oldSettingOrgIds   '原设置的部门Ids'
     *
     * @return bool
     */
    public function checkOrgManagePermission($newSettingOrgIds, $oldSettingOrgIds)
    {
        $newSettingOrgIds = $this->getOrgService()->wipeOffChildrenOrgIds($newSettingOrgIds);
        $oldSettingOrgIds = $this->getOrgService()->wipeOffChildrenOrgIds($oldSettingOrgIds);
        $deleteOrgIds = array_diff($oldSettingOrgIds, $newSettingOrgIds);
        $addOrgIds = array_diff($newSettingOrgIds, $oldSettingOrgIds);
        $diffOrgIds = array_merge($deleteOrgIds, $addOrgIds);

        $currentUserOrgIds = $this->getCurrentUser()->getManageOrgIdsRecursively();

        $diffOrgIds = array_diff($diffOrgIds, $currentUserOrgIds);

        return empty($diffOrgIds);
    }

    public function setUserManagePermissionOrgs($userId, $orgIds)
    {
        $this->beginTransaction();
        try {
            if (!empty($orgIds)) {
                $orgIds = $this->getOrgService()->wipeOffChildrenOrgIds($orgIds);
            }
            $this->deleteOrgRecordsByUserId($userId);

            $fields = array('userId' => $userId);
            foreach ($orgIds as $orgId) {
                $fields['orgId'] = $orgId;
                $this->createOrgRecord($fields);
            }

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * @param $userId
     *
     * @return array 获取用户有权限管理的OrgIds
     */
    public function findUserManageOrgIdsByUserId($userId)
    {
        $records = $this->getManagePermissionOrgDao()->findByUserId($userId);

        return ArrayToolkit::column($records, 'orgId');
    }

    /**
     * @param $userId
     *
     * @return array 获取用户设置的管理范围OrgCodes
     */
    public function findUserManageOrgCodesByUserId($userId)
    {
        $records = $this->getManagePermissionOrgDao()->findByUserId($userId);

        $orgs = $this->getOrgService()->findOrgsByIds(ArrayToolkit::column($records, 'orgId'));

        return ArrayToolkit::column($orgs, 'orgCode');
    }

    public function deleteOrgRecord($id)
    {
        return $this->getManagePermissionOrgDao()->delete($id);
    }

    public function deleteOrgRecordsByUserId($userId)
    {
        return $this->getManagePermissionOrgDao()->deleteByUserId($userId);
    }

    public function searchOrgRecords($conditions, $orderBys, $start, $limit)
    {
        return $this->getManagePermissionOrgDao()->search($conditions, $orderBys, $start, $limit);
    }

    public function findOrgRecordsByUserId($userId)
    {
        return $this->getManagePermissionOrgDao()->findByUserId($userId);
    }

    /**
     * @param string $type          使用授权类型
     * @param int    $resourceId    资源Id
     * @param int    $resourceOrgId 资源orgId
     *
     * @return bool
     *              判断资源是否能够被当前用户使用
     */
    public function checkResourceUsePermission($type, $resourceId, $resourceOrgId)
    {
        if (empty($resourceOrgId) || empty($resourceId)) {
            return false;
        }

        $usePermissionSharedRecord = $this->getResourceUsePermissionSharedService()->getSharedRecordByResourceIdAndResourceTypeAndToUserId($resourceId, $type, $this->getCurrentUser()->getId());

        if (!empty($usePermissionSharedRecord)) {
            return true;
        }

        if ($resourceOrgId) {
            $org = $this->getOrgService()->getOrg($resourceOrgId);

            return !empty($org) && $this->getCurrentUser()->hasManagePermissionWithOrgCode($org['orgCode']);
        }

        return false;
    }

    protected function filterFields($fields)
    {
        return ArrayToolkit::parts(
            $fields,
            array(
                'userId',
                'orgId',
                'createdUserId',
            )
        );
    }

    /**
     * @return ResourceUsePermissionSharedService
     */
    protected function getResourceUsePermissionSharedService()
    {
        return $this->createService('ResourceUsePermissionShared:ResourceUsePermissionSharedService');
    }

    /**
     * @return OrgService
     */
    protected function getOrgService()
    {
        return $this->createService('Org:OrgService');
    }

    /**
     * @return ManagePermissionOrgDao
     */
    protected function getManagePermissionOrgDao()
    {
        return $this->createDao('CorporateTrainingBundle:ManagePermission:ManagePermissionOrgDao');
    }
}
