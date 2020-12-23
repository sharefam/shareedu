<?php

namespace CorporateTrainingBundle\Biz\Exporter;

use AppBundle\Common\ArrayToolkit;

class OrgListExporter extends AbstractExporter
{
    public function canExport($parameters)
    {
        $biz = $this->biz;
        $user = $biz['user'];

        return $user->hasPermission('admin_org_manage');
    }

    public function getExportFileName()
    {
        return 'org.xls';
    }

    public function getSortedHeadingRow($parameters)
    {
        return array(
            array('code' => 'name', 'title' => $this->trans('admin.org_manage.org_name')),
            array('code' => 'code', 'title' => $this->trans('admin.org_manage.org_code')),
            array('code' => 'childrenNum', 'title' => $this->trans('admin.org_manage.org_childrenNum')),
            array('code' => 'parentCode', 'title' => $this->trans('admin.org_manage.org_parentCode')),
        );
    }

    public function buildExportData($parameters)
    {
        $org = $this->getOrgService()->getOrg($parameters['parentId']);
        $childrenOrgs = $this->getOrgService()->findOrgsByPrefixOrgCode($org['orgCode']);
        $orgIds = ArrayToolkit::column($childrenOrgs, 'id');
        $orgs = $this->getOrgService()->searchOrgs(array('orgIds' => $orgIds), array('depth' => 'ASC'), 0, PHP_INT_MAX);

        $orgData = array();
        foreach ($orgs as $org) {
            $parentOrg = $this->getOrgService()->getOrg($org['parentId']);
            $orgData[] = array(
                'name' => $org['name'],
                'code' => $org['code'],
                'childrenNum' => $org['childrenNum'],
                'parentCode' => $parentOrg['code'],
            );
        }

        $exportData[] = array(
            'data' => $orgData,
        );

        return $exportData;
    }
}
