<?php

namespace CorporateTrainingBundle\Biz\OrgSync\Service\Impl;

use Biz\BaseService;
use Biz\System\Service\LogService;
use CorporateTrainingBundle\Biz\OrgSync\Service\OrgSyncService;
use CorporateTrainingBundle\Component\EIMClient\DepartmentFactory;
use CorporateTrainingBundle\Common\Constant\CTConst;
use AppBundle\Common\ArrayToolkit;

class OrgSyncServiceImpl extends BaseService implements OrgSyncService
{
    public function syncFrom()
    {
        $result = array('success' => false, 'code' => 1);
        $setting = $this->getSettingService()->get('sync_department_setting', array());

        if (empty($setting) || !$setting['enable']) {
            return array('success' => false, 'code' => 2);
        }

        $client = DepartmentFactory::create($setting);
        $departments = $client->lists();

        if (!empty($departments['errcode'])) {
            $this->getLogService()->error('dingtalk', 'department_sync_from', $departments['errmsg']);

            return array('success' => false, 'code' => $departments['errcode'], 'message' => $departments['errmsg']);
        }

        if ($setting['times'] > 0) {
            $orgs = $this->getOrgService()->searchOrgs(array(), array(), 0, PHP_INT_MAX);
            $orgSyncIds = ArrayToolkit::column($orgs, 'syncId');
            if (in_array(0, $orgSyncIds)) {
                return array('success' => false, 'code' => 3);
            }
        }

        try {
            $this->beginTransaction();

            if ($setting['times'] <= 0) {
                $this->firstSyncFrom($departments);
            } else {
                $this->commonSyncFrom($departments);
            }

            $setting['syncTime'] = time();
            $setting['times'] = $setting['times'] + 1;
            $this->getSettingService()->set('sync_department_setting', $setting);

            $this->commit();
            $result = array('success' => true, 'code' => 0);
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }

        return $result;
    }

    private function firstSyncFrom($departments)
    {
        $departments = ArrayToolkit::index($departments, 'id');
        ksort($departments);
        $rootDepartment = array_shift($departments);
        $departmentGroups = ArrayToolkit::group($departments, 'parentid');
        ksort($departmentGroups);

        $this->getOrgService()->resetOrg(CTConst::ROOT_ORG_CODE);
        $rootOrg = $this->getOrgService()->getOrgByOrgCode(CTConst::ROOT_ORG_CODE);

        $fields = array(
            'name' => $rootDepartment['name'],
            'code' => $rootOrg['code'],
            'syncId' => $rootDepartment['id'],
        );
        $this->getOrgService()->updateOrg($rootOrg['id'], $fields);

        foreach ($departmentGroups as $key => $departmentGroup) {
            foreach ($departmentGroups[$key] as $department) {
                $department['code'] = 'dingtalk'.$department['id'];
                $department['parentId'] = $department['parentid'];
                $department['syncId'] = $department['id'];
                $departmentInfo = $this->getOrgService()->createOrg($department);

                if (isset($departmentGroups[$department['id']])) {
                    foreach ($departmentGroups[$department['id']] as &$childDepartment) {
                        $childDepartment['parentid'] = $departmentInfo['id'];
                    }
                }
            }
        }

        $this->initOrgsRelation();
    }

    private function commonSyncFrom($departments)
    {
        $departments = ArrayToolkit::index($departments, 'id');
        ksort($departments);

        array_shift($departments);
        $departmentGroups = ArrayToolkit::group($departments, 'parentid');
        ksort($departmentGroups);

        $orgs = $this->getOrgService()->searchOrgs(array(), array(), 0, PHP_INT_MAX);
        $orgs = ArrayToolkit::index($orgs, 'syncId');

        foreach ($departmentGroups as $key => $departmentGroup) {
            foreach ($departmentGroups[$key] as $department) {
                if (isset($orgs[$department['id']])) {
                    if ($orgs[$department['id']]['name'] != $department['name']) {
                        $fields = array(
                            'name' => $department['name'],
                            'code' => $orgs[$department['id']]['code'],
                        );
                        $this->getOrgService()->updateOrg($orgs[$department['id']]['id'], $fields);
                    }
                } else {
                    $parentOrg = $this->getOrgService()->getOrgBySyncId($department['parentid']);
                    if ($parentOrg) {
                        $department['code'] = 'dingtalk'.$department['id'];
                        $department['parentId'] = $parentOrg['id'];
                        $department['syncId'] = $department['id'];
                        $this->getOrgService()->createOrg($department);
                    } else {
                        $this->getLogService()->warning('org', 'sync_department_from', '同步失败，没有父机构与钉钉组织机构相对应', $department);
                    }
                }
            }
        }
    }

    private function initOrgsRelation()
    {
        $this->getUserService()->initOrgsRelation();
        $this->getCourseSetService()->initOrgsRelation();
        $this->getClassroomService()->initOrgsRelation();
        $this->getArticleService()->initOrgsRelation();
        $this->getNavigationService()->initOrgsRelation();
        $this->getCategoryService()->initOrgsRelation();
        $this->getTagService()->initOrgsRelation();
    }

    public function syncTo()
    {
        $result = array('success' => false, 'code' => 1);
        $setting = $this->getSettingService()->get('sync_department_setting', array());

        if (empty($setting) || !$setting['enable']) {
            $this->getLogService()->error('org', 'sync_department_to', '同步失败，请先开启同步开关', array());

            return array('successs' => false, 'code' => 2);
        }

        $client = DepartmentFactory::create($setting);
        $departments = $client->lists();

        if (count($departments) > 1) {
            $this->getLogService()->error('org', 'sync_department_to', '同步失败，请先清空钉钉下组织机构', array('count' => count($departments)));

            return array('success' => false, 'code' => 3);
        }

        $orgs = $this->getOrgService()->searchOrgs(array(), array('parentId' => 'ASC'), 0, PHP_INT_MAX);

        try {
            $this->beginTransaction();

            foreach ($orgs as $org) {
                if (1 == $org['id']) {
                    $this->updateOrgSync($org);
                    $department = $client->get($org['id']);
                    $org['syncId'] = $department['id'];
                    $this->getOrgService()->updateOrg($org['id'], $org);
                    continue;
                }
                $result = $this->createOrgSync($org);
                if (0 == $result['errcode']) {
                    $org['syncId'] = $result['id'];
                    $this->getOrgService()->updateOrg($org['id'], $org);
                }
            }

            $setting['syncTime'] = time();
            $setting['times'] = $setting['times'] + 1;
            $this->getSettingService()->set('sync_department_setting', $setting);

            $this->commit();
            $result = array('success' => true, 'code' => 0);
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }

        return $result;
    }

    public function createOrgSync($org)
    {
        $parentOrg = $this->getOrgService()->getOrg($org['parentId']);
        $org['parentid'] = $parentOrg['syncId'];
        $orgInfo = ArrayToolkit::parts(
            $org,
            array(
                'name',
                'parentid',
                'deptHiding',
                'deptPerimits',
                'userPerimits',
                'outerDept',
                'outerPermitDepts',
                'outerPermitUsers',
            )
        );

        if (!ArrayToolkit::requireds($orgInfo, array('name'))) {
            throw $this->createInvalidArgumentException('Lack of required fields');
        }

        $setting = $this->getSettingService()->get('sync_department_setting', array());

        if (empty($setting) || !$setting['enable']) {
            return;
        }

        $client = DepartmentFactory::create($setting);
        $result = $client->create($orgInfo);

        if (0 === $result['errcode']) {
            $fields = array(
                'name' => $org['name'],
                'code' => $org['code'],
                'syncId' => $result['id'],
            );
            $this->getOrgService()->updateOrg($org['id'], $fields);
            $this->getLogService()->info('org-sync', 'create-org', "创建同步部门(#{$result['id']})", $org);
        } else {
            $this->getLogService()->error('org-sync', 'create-org', '同步借口调用失败', $result);
        }

        return $result;
    }

    public function updateOrgSync($org)
    {
        $parentOrg = $this->getOrgService()->getOrg($org['parentId']);
        if ($parentOrg) {
            $org['parentid'] = $parentOrg['syncId'];
        }
        $org['id'] = $org['syncId'];

        $org = ArrayToolkit::parts(
            $org,
            array(
                'id',
                'name',
                'parentid',
                'autoAddUser',
                'deptHiding',
                'deptPerimits',
                'userPerimits',
                'outerDept',
                'outerPermitDepts',
                'outerPermitUsers',
            )
        );
        $org['autoAddUser'] = true;

        if (!ArrayToolkit::requireds($org, array('name'))) {
            throw $this->createInvalidArgumentException('Lack of required fields');
        }

        $setting = $this->getSettingService()->get('sync_department_setting', array());

        if (empty($setting) || !$setting['enable']) {
            return;
        }

        $client = DepartmentFactory::create($setting);

        $result = $client->update($org);

        if (0 === $result['errcode']) {
            $this->getLogService()->info('org-sync', 'update-org', "更新同步部门(#{$org['id']})", $org);
        } else {
            $this->getLogService()->error('org-sync', 'update-org', '同步借口调用失败', $result);
        }

        return $result;
    }

    public function deleteOrgSync($id)
    {
        $setting = $this->getSettingService()->get('sync_department_setting', array());

        if (empty($setting) || !$setting['enable']) {
            return;
        }

        $client = DepartmentFactory::create($setting);

        $result = $client->delete($id);

        if (0 === $result['errcode']) {
            $this->getLogService()->info('org-sync', 'delete-org', "删除同步部门(#{$id})", array('id' => $id));
        } else {
            $this->getLogService()->error('org-sync', 'delete-org', '删除借口调用失败', $result);
        }

        return $result;
    }

    protected function getOrgService()
    {
        return $this->createService('Org:OrgService');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    protected function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }

    protected function getTagService()
    {
        return $this->createService('Taxonomy:TagService');
    }

    protected function getNavigationService()
    {
        return $this->createService('Content:NavigationService');
    }

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    protected function getArticleService()
    {
        return $this->createService('Article:ArticleService');
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
