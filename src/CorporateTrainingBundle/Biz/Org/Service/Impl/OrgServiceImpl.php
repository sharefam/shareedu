<?php

namespace CorporateTrainingBundle\Biz\Org\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use CorporateTrainingBundle\Biz\ManagePermission\Service\ManagePermissionOrgService;
use CorporateTrainingBundle\Biz\Post\Util\ChineseFirstCharter;
use CorporateTrainingBundle\Common\Constant\CTConst;
use CorporateTrainingBundle\Biz\Org\Service\OrgService;
use Biz\Org\Service\Impl\OrgServiceImpl as BaseOrgServiceImpl;
use CorporateTrainingBundle\Common\OrgTreeToolkit;
use Codeages\Biz\Framework\Event\Event;

class OrgServiceImpl extends BaseOrgServiceImpl implements OrgService
{
    protected $visibleOrgTreeDataLocalCacheArray = array();

    public function createOrg($org)
    {
        $user = $this->getCurrentUser();

        $org = ArrayToolkit::parts($org, array('name', 'code', 'parentId', 'description', 'syncId'));

        if (!ArrayToolkit::requireds($org, array('name', 'code'))) {
            throw $this->createInvalidArgumentException('Lack of required fields');
        }

        $org['createdUserId'] = $user['id'];

        $org = $this->getOrgDao()->create($org);

        $parentOrg = $this->updateParentOrg($org);

        $org = $this->updateOrgCodeAndDepth($org, $parentOrg);

        $this->dispatchEvent('org.create', new Event($org));

        return $org;
    }

    public function batchCreateOrg($orgNames, $parentId)
    {
        $chineseFirstCharter = new ChineseFirstCharter();
        foreach ($orgNames as $name) {
            $org = $this->getOrgDao()->getByNameAndParentId($name, $parentId);
            if (empty($org)) {
                $fields['name'] = $name;
                $fields['parentId'] = $parentId;
                $fields['code'] = $chineseFirstCharter->getFirstCharters($name);
                $count = $this->countOrgs(array('code' => $fields['code']));
                if ($count >= 1) {
                    $fields['code'] = $fields['code'].'repeat';
                    $org = $this->createOrg($fields);
                    $org['code'] = str_replace('repeat', $org['id'], $org['code']);
                    $this->updateOrg($org['id'], $org);
                } else {
                    $this->createOrg($fields);
                }
            }
        }
    }

    public function updateOrg($id, $fields)
    {
        $this->checkBeforeProccess($id);

        $fields = ArrayToolkit::parts($fields, array('name', 'code', 'parentId', 'description', 'syncId'));

        if (!ArrayToolkit::requireds($fields, array('name', 'code'))) {
            throw $this->createInvalidArgumentException('Lack of required fields');
        }

        $org = $this->getOrgDao()->update($id, $fields);

        $this->dispatchEvent('org.update', new Event($org));

        return $org;
    }

    public function deleteOrg($id)
    {
        $org = $this->checkBeforeProccess($id);

        try {
            $this->biz['db']->beginTransaction();

            if ($org['parentId']) {
                $this->getOrgDao()->wave(array($org['parentId']), array('childrenNum' => -1));
            }

            $this->getOrgDao()->delete($id);
            //删除辖下
            $this->getOrgDao()->deleteByPrefixOrgCode($org['orgCode']);
            $this->dispatchEvent('org.delete', new Event($org));
            $this->biz['db']->commit();
        } catch (\Exception $e) {
            $this->biz['db']->rollback();
            throw $e;
        }
    }

    public function countOrgs(array $conditions)
    {
        return $this->getOrgDao()->count($conditions);
    }

    public function getOrgBySyncId($syncId)
    {
        return $this->getOrgDao()->getOrgBySyncId($syncId);
    }

    public function findOrgsBySyncIds($syncIds)
    {
        return $this->getOrgDao()->findOrgsBySyncIds($syncIds);
    }

    public function findOrgsByOrgCodes(array $orgCodes)
    {
        return $this->getOrgDao()->findByOrgCodes($orgCodes);
    }

    public function findOrgsByCodes(array $codes)
    {
        return $this->getOrgDao()->findByCodes($codes);
    }

    public function findSelfAndParentOrgsByOrgCode($orgCode)
    {
        $preOrgIdArray = explode('.', $orgCode);

        return $this->findOrgsByIds($preOrgIdArray);
    }

    public function findOrgsByPrefixOrgCodes(array $orgCodes = array('1.'), $columns = array())
    {
        return $this->getOrgDao()->findByPrefixOrgCodes($orgCodes, $columns);
    }

    public function resetOrg()
    {
        $currentUser = $this->getCurrentUser();

        if ($currentUser->isSuperAdmin() || $currentUser->hasPermission('admin_org_sync_department')) {
            $org = $this->getOrg(CTConst::ROOT_ORG_CODE);
            $this->getOrgDao()->wave(array($org['id']), array('childrenNum' => -$org['childrenNum']));
            $this->getOrgDao()->update($org['id'], array('syncId' => 0));

            return $this->getOrgDao()->deleteOrgsWithoutOrgCode(CTConst::ROOT_ORG_CODE);
        }

        return false;
    }

    public function buildOrgTreeByCode($orgCode)
    {
        if (empty($orgCode)) {
            return array();
        }

        $orgInfo = $this->getOrgByOrgCode($orgCode);

        $orgs = $this->findOrgsByPrefixOrgCode($orgCode);

        $orgs = ArrayToolkit::index($orgs, 'id');

        for ($i = CTConst::ORG_MAX_DEPTH; $i >= ($orgInfo['depth'] + 1); --$i) {
            foreach ($orgs as $key => $org) {
                if (!empty($org['depth']) && $org['depth'] == $i && $org['depth'] > 1) {
                    $orgs[$org['parentId']]['nodes'][] = $org;
                    unset($orgs[$key]);
                }
            }
        }

        return array_values($orgs);
    }

    public function buildVisibleOrgTreeByOrgCodes(array $orgCodes)
    {
        return array_values(OrgTreeToolkit::makeTree($this->getVisibleOrgTreeDataByOrgCodes($orgCodes, true)));
    }

    /**
     * @param $settingOrgIds
     * 被设置的用户或资源的组织机构orgIds
     * @param bool $filter
     *
     * @return array|mixed
     *
     * 部门设置特定树结构(展示管理员的管理范围及被操作用户的超出管理员权限部分的部门,组织机构树中超出我管理范围的部分只展示顶级)
     */
    public function getPermissionOrgTreeData($settingOrgIds, $filter = false)
    {
        $orgCodes = $this->getCurrentUser()->getManageOrgCodes();
        if (empty($orgCodes)) {
            return array();
        }
        $cacheKey = $this->getPermissionOrgTreeDataLocalCacheKey($orgCodes, $settingOrgIds, $filter);

        if (isset($this->visibleOrgTreeDataLocalCacheArray[$cacheKey])) {
            return $this->visibleOrgTreeDataLocalCacheArray[$cacheKey];
        }

        $orgs = $this->findOrgsByPrefixOrgCodes($orgCodes);
        $orgCodes = ArrayToolkit::column($orgs, 'orgCode');
        $settingOrgs = $this->findOrgsByIds($settingOrgIds);
        $settingOrgCodes = ArrayToolkit::column($settingOrgs, 'orgCode');

        $diffOrgCodes = array_diff($settingOrgCodes, $orgCodes);

        if (in_array('1.', $diffOrgCodes)) {
            $parentOrg = $this->getOrgByOrgCode('1.');
            $allOrgs = $this->findOrgsByParentId($parentOrg['id']);
            $allOrgs = array_merge($allOrgs, array($parentOrg), $orgs);
            if ($filter) {
                $allOrgs = $this->filterOrgs($allOrgs);
            }
            foreach ($allOrgs as &$org) {
                $org['selectable'] = false;
            }
        } else {
            $diffOrgs = $this->findOrgsByOrgCodes(array_values($diffOrgCodes));
            $orgCodeIds = $this->exploreOrgCodes($orgCodes);
            $parentOrgIds = array_diff($orgCodeIds, ArrayToolkit::column($orgs, 'id'));
            $parentOrgs = $this->findOrgsByIds(array_values($parentOrgIds));

            $allOrgs = array_merge($orgs, $parentOrgs, $diffOrgs);
            if ($filter) {
                $allOrgs = $this->filterOrgs($allOrgs);
            }
            $diffOrgTreeOrgCodes = array();
            if (!empty($diffOrgCodes)) {
                $diffOrgTree = $this->findOrgsByPrefixOrgCodes($diffOrgCodes, array('orgCode'));
                $diffOrgTreeOrgCodes = ArrayToolkit::column($diffOrgTree, 'orgCode');
            }
            foreach ($allOrgs as &$org) {
                if (in_array($org['id'], $parentOrgIds) || in_array($org['orgCode'], $diffOrgTreeOrgCodes)) {
                    $org['selectable'] = false;
                } else {
                    $org['selectable'] = true;
                }
            }
        }

        $indexedAllOrgs = ArrayToolkit::index($allOrgs, 'orgCode');
        ksort($indexedAllOrgs);

        $treeData = array_values($indexedAllOrgs);

        $this->visibleOrgTreeDataLocalCacheArray[$cacheKey] = $treeData;

        return $treeData;
    }

    /*
     * 获取可见的组织机构树组件数据，直线上级及所有下级
     */
    public function getVisibleOrgTreeDataByOrgCodes(array $orgCodes, $filter = false)
    {
        $cacheKey = $this->getVisibleOrgTreeDataLocalCacheKey($orgCodes, $filter);

        if (isset($this->visibleOrgTreeDataLocalCacheArray[$cacheKey])) {
            return $this->visibleOrgTreeDataLocalCacheArray[$cacheKey];
        }

        $orgs = $this->findOrgsByPrefixOrgCodes($orgCodes);
        $orgCodes = ArrayToolkit::column($orgs, 'orgCode');
        $orgCodeIds = $this->exploreOrgCodes($orgCodes);

        $parentOrgIds = array_diff($orgCodeIds, ArrayToolkit::column($orgs, 'id'));
        $parentOrgs = $this->findOrgsByIds(array_values($parentOrgIds));

        $allOrgs = array_merge($orgs, $parentOrgs);

        if ($filter) {
            $allOrgs = $this->filterOrgs($allOrgs);
        }

        foreach ($allOrgs as &$org) {
            if (in_array($org['id'], $parentOrgIds)) {
                $org['selectable'] = false;
            } else {
                $org['selectable'] = true;
            }
        }

        $indexedAllOrgs = ArrayToolkit::index($allOrgs, 'orgCode');
        ksort($indexedAllOrgs);

        $treeData = array_values($indexedAllOrgs);

        $this->visibleOrgTreeDataLocalCacheArray[$cacheKey] = $treeData;

        return $treeData;
    }

    protected function getVisibleOrgTreeDataLocalCacheKey($orgCodes, $filter)
    {
        return json_encode($orgCodes).'|'.($filter ? '1' : '0');
    }

    protected function getPermissionOrgTreeDataLocalCacheKey($orgCodes, $settingOrgIds, $filter)
    {
        return json_encode($settingOrgIds).'|'.json_encode($orgCodes).'|'.($filter ? '1' : '0');
    }

    public function findOrgsByParentId($orgId)
    {
        return $this->getOrgDao()->findByParentOrgId($orgId);
    }

    /**
     * @param $orgIds
     *
     * @return array 过滤子部门获取顶层部门
     */
    public function wipeOffChildrenOrgIds($orgIds)
    {
        $orgs = $this->findOrgsByIds($orgIds);
        $orgs = ArrayToolkit::index($orgs, 'id');
        $orgIds = array();
        foreach ($orgs as $org) {
            if (empty($orgs[$org['parentId']])) {
                $orgIds[] = $org['id'];
            }
        }

        return $orgIds;
    }

    protected function filterOrgs($orgs)
    {
        foreach ($orgs as $key => $org) {
            $orgs[$key] = ArrayToolkit::parts($org, array(
                'id',
                'name',
                'parentId',
                'seq',
                'orgCode',
                'code',
                'depth',
            ));
        }

        return $orgs;
    }

    protected function exploreOrgCodes(array $orgCodes)
    {
        $orgIds = array();

        foreach ($orgCodes as $orgCode) {
            $orgIds = array_merge($orgIds, array_filter(explode('.', $orgCode)));
        }

        return array_unique($orgIds);
    }

    private function updateParentOrg($org)
    {
        $parentOrg = null;

        if (isset($org['parentId']) && $org['parentId'] > 0) {
            $parentOrg = $this->getOrgDao()->get($org['parentId']);
            $this->getOrgDao()->wave(array($parentOrg['id']), array('childrenNum' => +1));
        }

        return $parentOrg;
    }

    private function updateOrgCodeAndDepth($org, $parentOrg)
    {
        $fields = array();

        if (empty($parentOrg)) {
            $fields['orgCode'] = $org['id'].'.';
            $fields['depth'] = 1;
        } else {
            $fields['orgCode'] = $parentOrg['orgCode'].$org['id'].'.';
            $fields['depth'] = $parentOrg['depth'] + 1;
        }

        return $this->getOrgDao()->update($org['id'], $fields);
    }

    private function checkBeforeProccess($id)
    {
        $org = $this->getOrg($id);

        if (empty($org)) {
            throw $this->createNotFoundException('Org does not exist, update failed');
        }

        return $org;
    }

    /**
     * @return ManagePermissionOrgService
     */
    protected function getManagePermissionService()
    {
        return $this->createService('CorporateTrainingBundle:ManagePermission:ManagePermissionOrgService');
    }
}
