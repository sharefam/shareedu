<?php

namespace CorporateTrainingBundle\Biz\Importer;

use Symfony\Component\HttpFoundation\Request;
use Biz\Importer\Importer;

class OfflineExamResultImporter extends Importer
{
    protected $necessaryFields = array('nickname' => '用户名', 'score' => '成绩');
    protected $objWorksheet;
    protected $rowTotal = 0;
    protected $colTotal = 0;
    protected $excelFields = array();
    protected $passValidateUser = array();
    protected $offlineExamId = 0;
    protected $type = 'offline-exam-result';

    public function import(Request $request)
    {
        $importData = $request->request->get('importData');
        $projectPlanId = $request->request->get('projectPlanId');
        $offlineExamId = $request->request->get('offlineExamId');
        $projectPlan = $this->getProjectPlanService()->getProjectPlan($projectPlanId);

        return $this->excelDataImporting($projectPlan, $offlineExamId, $importData);
    }

    protected function excelDataImporting($projectPlan, $offlineExamId, $userData)
    {
        $successCount = 0;
        $offlineExam = $this->getOfflineExamService()->getOfflineExam($offlineExamId);

        foreach ($userData as $key => $data) {
            if (!empty($data['nickname'])) {
                $user = $this->getUserService()->getUserByNickname($data['nickname']);
            }
            if (!empty($user)) {
                if ($data['score'] >= $offlineExam['score']) {
                    $data['score'] = $offlineExam['score'];
                }
                if ($offlineExam['passScore'] <= $data['score']) {
                    $result = $this->getOfflineExamMemberService()->markPass($offlineExamId, $user['id'], $data['score']);
                } else {
                    $result = $this->getOfflineExamMemberService()->markUnPass($offlineExamId, $user['id'], $data['score']);
                }
                ++$successCount;
                $this->getLogService()->info('offline_exam', 'add_result', "培训项目《{$projectPlan['name']}》(#{$projectPlan['id']})，添加学员{$user['nickname']}(#{$user['id']})的线下考试{$offlineExam['title']}(#{$offlineExam['id']})成绩，备注：通过批量导入添加");
            }
        }

        return array('successCount' => $successCount);
    }

    public function check(Request $request)
    {
        $file = $request->files->get('excel');
        $projectPlanId = $request->request->get('projectPlanId');
        $offlineExamId = $request->request->get('offlineExamId');
        $this->offlineExamId = $offlineExamId;
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
                'projectPlanId' => $projectPlanId,
                'offlineExamId' => $offlineExamId,
            ));
    }

    protected function getUserData()
    {
        $userCount = 0;
        $fieldSort = $this->getFieldArray();
        $validate = array();
        $allUserData = array();

        for ($row = 2; $row <= $this->rowTotal; ++$row) {
            for ($col = 0; $col < $this->colTotal; ++$col) {
                $infoData = $this->objWorksheet->getCellByColumnAndRow($col, $row)->getFormattedValue();
                $columnsData[$col] = $infoData.'';
            }

            foreach ($fieldSort as $sort) {
                $userData[$sort['fieldName']] = trim($columnsData[$sort['num']]);
                $fieldCol[$sort['fieldName']] = $sort['num'] + 1;
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

        $offlineExam = $this->getOfflineExamService()->getOfflineExam($this->offlineExamId);

        if (!empty($userData['nickname'])) {
            $user = $this->getUserService()->getUserByNickname($userData['nickname']);
        }

        if (!$user) {
            $errorInfo = $this->getServiceKernel()->trans('importer.org.check.repeat_row_info.row_user_data_error', array('%row%' => $row));
        }

        if ('' == $this->trim($userData['score'])) {
            $errorInfo = $this->getServiceKernel()->trans('importer.org.check.repeat_row_info.row_user_score_error', array('%row%' => $row));
        }

        if ($userData['score'] > $offlineExam['score']) {
            $errorInfo = $this->getServiceKernel()->trans('importer.org.check.repeat_row_info.row_user_score_error_two', array('%row%' => $row));
        }

        return $errorInfo;
    }

    protected function getFieldArray()
    {
        $fieldArray = array();
        $fields = array(
            'truename' => '姓名',
            'nickname' => '用户名',
            'org' => '部门',
            'post' => '岗位',
            'score' => '成绩（必填）',
            'status' => '考试结果（非必填）',
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
        $projectPlanId = $request->query->get('id');
        $offlineExamId = $request->query->get('offlineExamId');

        return $this->render('project-plan/exam-manage/offline-exam/exam-result-import.html.twig', array(
            'projectPlanId' => $projectPlanId,
            'offlineExamId' => $offlineExamId,
            'importerType' => $this->type,
        ));
    }

    public function tryImport(Request $request)
    {
        $this->getProjectPlanService()->hasManageProjectPlanPermission();
    }

    protected function getProjectPlanMemberService()
    {
        return $this->getServiceKernel()->getBiz()->service('CorporateTrainingBundle:ProjectPlan:MemberService');
    }

    protected function getProjectPlanService()
    {
        return $this->getServiceKernel()->getBiz()->service('CorporateTrainingBundle:ProjectPlan:ProjectPlanService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->getBiz()->service('CorporateTrainingBundle:User:UserService');
    }

    protected function getLogService()
    {
        return $this->getServiceKernel()->getBiz()->service('System:LogService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineExam\Service\Impl\OfflineExamServiceImpl
     */
    protected function getOfflineExamService()
    {
        return $this->getServiceKernel()->getBiz()->service('CorporateTrainingBundle:OfflineExam:OfflineExamService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineExam\Service\Impl\MemberServiceImpl
     */
    protected function getOfflineExamMemberService()
    {
        return $this->getServiceKernel()->getBiz()->service('CorporateTrainingBundle:OfflineExam:MemberService');
    }
}
