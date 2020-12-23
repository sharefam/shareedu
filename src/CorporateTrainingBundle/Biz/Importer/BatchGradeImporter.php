<?php

namespace CorporateTrainingBundle\Biz\Importer;

use Symfony\Component\HttpFoundation\Request;
use Biz\Importer\Importer;

class BatchGradeImporter extends Importer
{
    protected $necessaryFields = array('nickname' => '用户名');
    protected $objWorksheet;
    protected $rowTotal = 0;
    protected $colTotal = 0;
    protected $excelFields = array();
    protected $passValidateUser = array();

    protected $type = 'batch-grade';

    public function import(Request $request)
    {
        $importData = $request->request->get('importData');
        $offlineActivityId = $request->request->get('offlineActivityId');
        $offlineActivity = $this->getOfflineActivityService()->getOfflineActivity($offlineActivityId);

        return $this->excelDataImporting($offlineActivity, $importData);
    }

    protected function excelDataImporting($targetObject, $userData)
    {
        $existsUserCount = 0;
        $successCount = 0;

        foreach ($userData as $key => $user) {
            $isMember = $this->getOfflineActivityMemberService()->isMember($targetObject['id'], $user['id']);

            if ($isMember) {
                ++$existsUserCount;
                $member = $this->getOfflineActivityMemberService()->getMemberByActivityIdAndUserId($targetObject['id'], $user['id']);

                if (!empty($user['attendedStatus'])) {
                    $fields['attendedStatus'] = ('T' == strtoupper($user['attendedStatus'])) ? 'attended' : 'unattended';
                }

                if (0 === $user['score']) {
                    $fields['score'] = 0;
                } else {
                    if (!empty($user['score'])) {
                        $fields['score'] = $user['score'];
                    }
                }

                if (!empty($user['passedStatus'])) {
                    $fields['passedStatus'] = ('T' == strtoupper($user['passedStatus'])) ? 'passed' : 'unpassed';
                }

                if (isset($user['evaluate'])) {
                    $fields['evaluate'] = $user['evaluate'];
                }

                if ($this->getOfflineActivityMemberService()->updateMember($member['id'], $fields)) {
                    ++$successCount;
                }

                unset($fields);

                $this->getLogService()->info('offline_activity', 'update_member', "活动《{$targetObject['title']}》(#{$targetObject['id']})，编辑学员{$user['nickname']}(#{$user['id']})，备注：通过批量考勤评价");
            }
        }

        return array('existsUserCount' => $existsUserCount, 'successCount' => $successCount);
    }

    public function check(Request $request)
    {
        $file = $request->files->get('excel');
        $offlineActivityId = $request->request->get('offlineActivityId');

        $danger = $this->validateExcelFile($file);
        if (!empty($danger)) {
            return $danger;
        }

        $repeatInfo = $this->checkRepeatData(1);
        if (!empty($repeatInfo)) {
            return $this->createErrorResponse($repeatInfo);
        }

        $importData = $this->getUserData();

        if (empty($importData['errorInfo'])) {
            $passedRepeatInfo = $this->checkPassedRepeatData();
            if ($passedRepeatInfo) {
                return $this->createErrorResponse($passedRepeatInfo);
            }
        } else {
            return $this->createErrorResponse($importData['errorInfo']);
        }

        return $this->createSuccessResponse(
            $importData['allUserData'],
            $importData['checkInfo'],
            array(
                'offlineActivityId' => $offlineActivityId,
            ));
    }

    protected function getUserData()
    {
        $userCount = 0;
        $fieldArray = $this->getFieldArray();
        $validate = array();
        $allUserData = array();

        for ($row = 2; $row <= $this->rowTotal; ++$row) {
            for ($col = 0; $col < $this->colTotal; ++$col) {
                $infoData = $this->objWorksheet->getCellByColumnAndRow($col, $row)->getFormattedValue();
                $columnsData[$col] = $infoData.'';
            }

            foreach ($fieldArray as $field) {
                $userData[$field['fieldName']] = trim($columnsData[$field['num']]);
                $fieldCol[$field['fieldName']] = $field['num'] + 1;
            }

            $emptyData = array_count_values($userData);

            if (isset($emptyData['']) && count($userData) == $emptyData['']) {
                $checkInfo[] = $this->getServiceKernel()->trans('importer.check.repeat_row_info.row_empty', array('%row%' => $row));
                continue;
            }

            $info = $this->validExcelFieldValue($userData, $row, $fieldCol);

            empty($info) ? '' : $errorInfo[] = $info;

            $userCount = $userCount + 1;

            $allUserData[] = $userData;

            if (empty($errorInfo)) {
                if (!empty($userData['nickname'])) {
                    $user = $this->getUserService()->getUserByNickname($userData['nickname']);
                }

                $validate[] = array_merge($user, array('row' => $row), $userData);
            }

            unset($userData);
        }

        $this->passValidateUser = $validate;

        $data['errorInfo'] = empty($errorInfo) ? array() : $errorInfo;
        $data['checkInfo'] = empty($checkInfo) ? array() : $checkInfo;
        $data['userCount'] = $userCount;
        $data['allUserData'] = empty($this->passValidateUser) ? array() : $this->passValidateUser;

        return $data;
    }

    protected function validExcelFieldValue($userData, $row, $fieldCol)
    {
        $errorInfo = '';

        if (!empty($userData['nickname'])) {
            $user = $this->getUserService()->getUserByNickname($userData['nickname']);
        }

        if (!$user) {
            $errorInfo = $this->getServiceKernel()->trans('importer.org.check.repeat_row_info.row_user_data_error', array('%row%' => $row));
        }

        return $errorInfo;
    }

    protected function getFieldArray()
    {
        $fieldArray = array();
        $fields = array(
            'nickname' => '用户名',
            'attendedStatus' => '出勤情况',
            'score' => '成绩',
            'evaluate' => '表现评价',
            'passedStatus' => '考核结果',
        );

        $excelFields = $this->excelFields;

        foreach ($excelFields as $key => $value) {
            if (in_array($value, $fields)) {
                foreach ($fields as $fieldKey => $fieldValue) {
                    if ($value == $fieldValue) {
                        $fieldArray[$fieldKey] = array('num' => $key, 'fieldName' => $fieldKey);
                        break;
                    }
                }
            }
        }

        return $fieldArray;
    }

    protected function excelAnalyse($file)
    {
        $objPHPExcel = \PHPExcel_IOFactory::load($file);
        //        $objWorksheet = $objPHPExcel->getActiveSheet();
        $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
        $highestRow = $objWorksheet->getHighestRow();
        $highestColumn = $objWorksheet->getHighestColumn();
        $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);
        $excelFields = array();

        for ($col = 0; $col < $highestColumnIndex; ++$col) {
            $fieldTitle = $objWorksheet->getCellByColumnAndRow($col, 1)->getValue();
            empty($fieldTitle) ? '' : $excelFields[$col] = $this->trim($fieldTitle);
        }

        $rowAndCol = array('rowLength' => $highestRow, 'colLength' => $highestColumnIndex);

        $this->objWorksheet = $objWorksheet;
        $this->rowTotal = $highestRow;
        $this->colTotal = $highestColumnIndex;
        $this->excelFields = $excelFields;

        return array($objWorksheet, $rowAndCol, $excelFields);
    }

    public function getTemplate(Request $request)
    {
        $offlineActivityId = $request->query->get('offlineActivityId');
        $offlineActivity = $this->getOfflineActivityService()->getOfflineActivity($offlineActivityId);

        return $this->render('offline-activity-manage/batch-grade-import.html.twig', array(
            'offlineActivity' => $offlineActivity,
            'importerType' => $this->type,
        ));
    }

    public function tryImport(Request $request)
    {
        $this->getOfflineActivityService()->hasActivityManageRole();
    }

    protected function getOfflineActivityService()
    {
        return $this->getServiceKernel()->getBiz()->service('CorporateTrainingBundle:OfflineActivity:OfflineActivityService');
    }

    protected function getOfflineActivityMemberService()
    {
        return $this->getServiceKernel()->getBiz()->service('CorporateTrainingBundle:OfflineActivity:MemberService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->getBiz()->service('CorporateTrainingBundle:User:UserService');
    }

    protected function getLogService()
    {
        return $this->getServiceKernel()->getBiz()->service('System:LogService');
    }
}
