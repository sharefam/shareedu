<?php

namespace CorporateTrainingBundle\Biz\Importer;

use Symfony\Component\HttpFoundation\Request;
use Biz\Importer\Importer;

class OrgImporter extends Importer
{
    protected $necessaryFields = array('name' => '名称', 'code' => '编码', 'description' => '描述(选填)', 'parentCode' => '父级机构编码');
    protected $objWorksheet;
    protected $rowTotal = 0;
    protected $colTotal = 0;
    protected $excelFields = array();
    protected $passValidateOrg = array();
    protected $targetType = array();
    protected $targetId = array();
    protected $type = 'org';
    protected $needValidateFields = array('code');
    protected $codes = array();

    public function import(Request $request)
    {
        $importData = $request->request->get('importData');

        $successCount = 0;
        foreach ($importData as $key => $data) {
            $org = $this->getOrgService()->getOrgByCode($data['code']);
            $parentOrg = $this->getOrgService()->getOrgByCode($data['parentCode']);

            $isAvaliable = $this->getOrgService()->isNameAvaliable($data['name'], $parentOrg['id'], null);
            if (empty($org) && !empty($parentOrg) && $isAvaliable) {
                $fields = array(
                    'name' => $data['name'],
                    'code' => $data['code'],
                    'description' => $data['description'],
                    'parentId' => $parentOrg['id'],
                );
                $org = $this->getOrgService()->createOrg($fields);
                ++$successCount;
                $this->getLogService()->info('org_import', 'org_import', "导入组织机构《{$org['name']}》(#{$org['id']})");
            }
        }

        return array('successCount' => $successCount);
    }

    public function check(Request $request)
    {
        $file = $request->files->get('excel');
        $danger = $this->validateExcelFile($file);
        if (!empty($danger)) {
            return $danger;
        }

        $repeatInfo = $this->checkRepeatData();
        $importData = $this->getOrgData();
        $importData['checkInfo'] = array_merge($importData['checkInfo'], $repeatInfo);

        if (empty($importData['errorInfo'])) {
            $passedRepeatInfo = $this->checkPassedRepeatData();
            $importData['checkInfo'] = array_merge($importData['checkInfo'], $passedRepeatInfo);
        } else {
            return $this->createErrorResponse($importData['errorInfo']);
        }
        $allOrgData = array();
        foreach ($importData['allOrgData'] as $value) {
            $org = $this->getOrgService()->getOrgByCode($value['code']);

            if (empty($org)) {
                $allOrgData[] = $value;
            }
        }

        return $this->createSuccessResponse(
            $allOrgData,
            $importData['checkInfo']
        );
    }

    protected function getOrgData()
    {
        $orgCount = 0;
        $fieldSort = $this->getFieldSort();
        $validate = array();
        $allOrgData = array();

        for ($row = 3; $row <= $this->rowTotal; ++$row) {
            for ($col = 0; $col < $this->colTotal; ++$col) {
                $infoData = $this->objWorksheet->getCellByColumnAndRow($col, $row)->getFormattedValue();
                $columnsData[$col] = $infoData.'';
            }

            foreach ($fieldSort as $sort) {
                $orgData[$sort['fieldName']] = trim($columnsData[$sort['num']]);
                $fieldCol[$sort['fieldName']] = $sort['num'] + 1;
            }

            $emptyData = array_count_values($orgData);

            if (isset($emptyData['']) && count($orgData) == $emptyData['']) {
                $checkInfo[] = $this->getServiceKernel()->trans('importer.check.repeat_row_info.row_empty', array('%row%' => $row));
                continue;
            }

            $info = $this->validExcelFieldValue($orgData, $row, $fieldCol);
            empty($info['errorInfo']) ? '' : $errorInfo[] = $info['errorInfo'];

            $orgCount = $orgCount + 1;

            $allOrgData[] = $orgData;

            if (empty($info['errorInfo'])) {
                $validate[] = array_merge($orgData, array('row' => $row));
            }

            unset($orgData);
        }

        $this->passValidateOrg = $validate;

        $data['errorInfo'] = empty($errorInfo) ? array() : $errorInfo;
        $data['checkInfo'] = empty($checkInfo) ? array() : $checkInfo;
        $data['orgCount'] = $orgCount;
        $data['allOrgData'] = empty($this->passValidateOrg) ? array() : $this->passValidateOrg;

        return $data;
    }

    protected function validExcelFieldValue($userData, $row, $fieldCol)
    {
        $errorInfo = '';
        $user = null;
        $verifiedMobileUser = null;
        $emailUser = null;
        $nicknameUser = null;
        $codes = $this->codes;
        $org = $this->getOrgService()->getOrgByCode($userData['parentCode']);

        if ($userData['code'] == $userData['parentCode']) {
            $errorInfo = $this->getServiceKernel()->trans('importer.org.check.repeat_row_info.row_data_error', array('%row%' => $row, '%parentCode%' => $userData['parentCode']));
        }

        if (!(in_array($userData['parentCode'], $codes) || !empty($org))) {
            if (empty($parentOrg)) {
                $errorInfo = $this->getServiceKernel()->trans('importer.org.check.repeat_row_info.row_data_error', array('%row%' => $row, '%parentCode%' => $userData['parentCode']));
            }
        }

        return array('errorInfo' => $errorInfo, 'org' => $org);
    }

    public function getTemplate(Request $request)
    {
        return $this->render('admin/org-manage/org-importer.html.twig', array(
            'importerType' => $this->type,
        ));
    }

    public function tryImport(Request $request)
    {
        return true;
    }

    protected function getLogService()
    {
        return $this->getServiceKernel()->getBiz()->service('System:LogService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\Org\Service\Impl\OrgServiceImpl
     */
    protected function getOrgService()
    {
        return $this->getServiceKernel()->getBiz()->service('CorporateTrainingBundle:Org:OrgService');
    }
}
