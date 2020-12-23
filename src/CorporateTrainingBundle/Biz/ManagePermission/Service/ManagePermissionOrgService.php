<?php

namespace CorporateTrainingBundle\Biz\ManagePermission\Service;

interface ManagePermissionOrgService
{
    public function createOrgRecord($fields);

    public function updateOrgRecord($id, $fields);

    public function deleteOrgRecord($id);

    public function checkOrgManagePermission($newSettingOrgIds, $oldSettingOrgIds);

    public function searchOrgRecords($conditions, $orderBys, $start, $limit);

    public function findOrgRecordsByUserId($userId);

    public function setUserManagePermissionOrgs($userId, $orgIds);

    public function findUserManageOrgIdsByUserId($userId);

    public function findUserManageOrgCodesByUserId($userId);

    public function checkResourceUsePermission($type, $resourceId, $resourceOrgId);
}
