<?php

namespace CorporateTrainingBundle\Biz\Org\Service;

use Biz\Org\Service\OrgService as BaseInterface;

interface OrgService extends BaseInterface
{
    public function createOrg($org);

    public function batchCreateOrg($orgNames, $parentId);

    public function updateOrg($id, $fields);

    public function deleteOrg($id);

    public function countOrgs(array $conditions);

    public function getOrgBySyncId($syncId);

    public function findOrgsBySyncIds($syncIds);

    public function findOrgsByOrgCodes(array $orgCodes);

    public function findOrgsByCodes(array $codes);

    public function findSelfAndParentOrgsByOrgCode($orgCode);

    public function findOrgsByPrefixOrgCodes(array $orgCodes = array('1.'), $columns = array());

    public function resetOrg();

    public function buildOrgTreeByCode($orgCode);

    public function buildVisibleOrgTreeByOrgCodes(array $orgCodes);

    public function getVisibleOrgTreeDataByOrgCodes(array $orgCodes, $filter = false);

    public function findOrgsByParentId($orgId);
}
