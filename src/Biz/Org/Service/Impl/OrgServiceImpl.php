<?php

namespace Biz\Org\Service\Impl;

use Biz\BaseService;
use Biz\Org\Dao\OrgDao;
use Biz\Org\Service\OrgService;
use AppBundle\Common\ArrayToolkit;

class OrgServiceImpl extends BaseService implements OrgService
{
    public function createOrg($org)
    {
        $user = $this->getCurrentUser();

        $org = ArrayToolkit::parts($org, array('name', 'code', 'parentId', 'description'));

        if (!ArrayToolkit::requireds($org, array('name', 'code'))) {
            throw $this->createServiceException('缺少必要字段,添加失败');
        }

        $org['createdUserId'] = $user['id'];

        $org = $this->getOrgDao()->create($org);

        $parentOrg = $this->updateParentOrg($org);

        $org = $this->updateOrgCodeAndDepth($org, $parentOrg);

        return $org;
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

    public function updateOrg($id, $fields)
    {
        $org = $this->checkBeforeProccess($id);

        $fields = ArrayToolkit::parts($fields, array('name', 'code', 'parentId', 'description'));

        if (!ArrayToolkit::requireds($fields, array('name', 'code'))) {
            throw $this->createServiceException('缺少必要字段,添加失败');
        }

        $org = $this->getOrgDao()->update($id, $fields);

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
            $this->biz['db']->commit();
        } catch (\Exception $e) {
            $this->biz['db']->rollback();
            throw $e;
        }
    }

    public function switchOrg($id)
    {
        $user = $this->getCurrentUser();

        $data = $user->toArray();
        $data['selectOrg'] = $this->checkBeforeProccess($id);
        $user->fromArray($data);
        $this->biz['user'] = $user;
    }

    public function getOrgByOrgCode($orgCode)
    {
        return $this->getOrgDao()->getByOrgCode($orgCode);
    }

    public function getOrg($id)
    {
        return $this->getOrgDao()->get($id);
    }

    public function findOrgsByIds($ids)
    {
        return $this->getOrgDao()->findByIds($ids);
    }

    public function findOrgsByPrefixOrgCode($orgCode = null)
    {
        //是否需要对该api做用户权限处理
        if (empty($orgCode)) {
            $user = $this->getCurrentUser();
            $org = $this->getOrg($user['orgId']);
            $orgCode = $org['orgCode'];
        }

        return $this->getOrgDao()->findByPrefixOrgCode($orgCode);
    }

    public function isCodeAvaliable($value, $exclude)
    {
        $org = $this->getOrgDao()->getByCode($value);

        if (empty($org)) {
            return true;
        }

        return $org['code'] === $exclude;
    }

    private function checkBeforeProccess($id)
    {
        $org = $this->getOrg($id);

        if (empty($org)) {
            throw $this->createServiceException('组织机构不存在,更新失败');
        }

        return $org;
    }

    public function sortOrg($ids)
    {
        foreach ($ids as $index => $id) {
            $this->getOrgDao()->update($id, array('seq' => $index));
        }
    }

    public function searchOrgs($conditions, $orderBy, $start, $limit, $columns = array())
    {
        return $this->getOrgDao()->search($conditions, $orderBy, $start, $limit, $columns);
    }

    public function getOrgByCode($code)
    {
        return $this->getOrgDao()->getByCode($code);
    }

    public function geFullOrgNameById($id, $orgs = array())
    {
        $orgs[] = $org = $this->getOrg($id);
        if (isset($org['parentId'])) {
            return $this->geFullOrgNameById($org['parentId'], $orgs);
        } else {
            $orgs = ArrayToolkit::index($orgs, 'id');
            ksort($orgs);
            $orgs = ArrayToolkit::column($orgs, 'name');

            return implode($orgs, '->');
        }
    }

    public function batchUpdateOrg($module, $ids, $orgCode)
    {
        $this->getModuleService($module)->batchUpdateOrg(explode(',', $ids), $orgCode);
    }

    public function isNameAvaliable($name, $parentId, $exclude)
    {
        $org = $this->getOrgDao()->getByNameAndParentId($name, $parentId);
        if (empty($org)) {
            return true;
        }

        return $org['id'] == $exclude;
    }

    public function findRelatedModuleCounts($orgId, $orgModules)
    {
        $org = $this->getOrg($orgId);
        $conditions = array('likeOrgCode' => $org['orgCode']);

        $moduleCounts = array();
        foreach ($orgModules as $module) {
            $callable = array($this->createService($module['service']), $module['countMethod']);
            $moduleCounts[$module['displayKey']] = call_user_func($callable, $conditions);
        }

        return array_filter($moduleCounts);
    }

    /**
     * @return OrgDao
     */
    public function getOrgDao()
    {
        return $this->createDao('Org:OrgDao');
    }

    protected function getModuleService($module)
    {
        return $this->createService($module['service']);
    }
}
