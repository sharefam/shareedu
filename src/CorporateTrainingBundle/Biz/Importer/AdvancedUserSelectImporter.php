<?php

namespace CorporateTrainingBundle\Biz\Importer;

use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Biz\Importer\Importer;

class AdvancedUserSelectImporter extends Importer
{
    protected $necessaryFields = array('nickname' => '用户名', 'verifiedMobile' => '手机', 'email' => '邮箱');
    protected $objWorksheet;
    protected $rowTotal = 0;
    protected $colTotal = 0;
    protected $excelFields = array();
    protected $passValidateUser = array();
    protected $targetType = array();
    protected $targetId = array();
    protected $notificationSetting = 1;
    protected $type = 'advanced-user-select';

    public function import(Request $request)
    {
        $importData = $request->request->get('importData');
        $this->targetType = $request->request->get('targetType');
        $this->targetId = $request->request->get('targetId');
        $this->notificationSetting = $request->request->get('notificationSetting');

        $selector = $this->getMemberSelector($this->targetType);

        $userIds = ArrayToolkit::column($importData, 'id');

        $result = $selector->selectUserIds($this->targetId, $userIds, $this->notificationSetting);

        return array('existsUserCount' => count($importData), 'successCount' => count($importData));
    }

    public function check(Request $request)
    {
        $file = $request->files->get('excel');

        $danger = $this->validateExcelFile($file);
        if (!empty($danger)) {
            return $danger;
        }

        $repeatInfo = $this->checkRepeatData();
        $importData = $this->getUserData();
        $importData['checkInfo'] = array_merge($importData['checkInfo'], $repeatInfo);

        if (empty($importData['errorInfo'])) {
            $passedRepeatInfo = $this->checkPassedRepeatData();
            $importData['checkInfo'] = array_merge($importData['checkInfo'], $passedRepeatInfo);
        } else {
            return $this->createErrorResponse($importData['errorInfo']);
        }

        return $this->createSuccessResponse(
            $importData['allUserData'],
            $importData['checkInfo'],
            array(
                'notificationSetting' => $this->notificationSetting,
                'targetType' => $this->targetType,
                'targetId' => $this->targetId,
            ));
    }

    protected function getUserData()
    {
        $userCount = 0;
        $fieldSort = $this->getFieldSort();
        $validate = array();
        $allUserData = array();

        for ($row = 3; $row <= $this->rowTotal; ++$row) {
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
            empty($info['errorInfo']) ? '' : $errorInfo[] = $info['errorInfo'];

            $userCount = $userCount + 1;

            $allUserData[] = $userData;

            if (empty($info['errorInfo'])) {
                $validate[] = array_merge(array('id' => $info['user']['id']), array('row' => $row));
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
        $user = null;
        $verifiedMobileUser = null;
        $emailUser = null;
        $nicknameUser = null;
        if (!empty($userData['verifiedMobile'])) {
            $verifiedMobileUser = $this->getUserService()->getUserByVerifiedMobile($userData['verifiedMobile']);
        }

        if (!empty($userData['email'])) {
            $emailUser = $this->getUserService()->getUserByEmail($userData['email']);
        }

        if (!empty($userData['nickname'])) {
            $nicknameUser = $this->getUserService()->getUserByNickname($userData['nickname']);
        }

        if (!empty($verifiedMobileUser)) {
            $user = $verifiedMobileUser;
        } elseif (!empty($emailUser)) {
            $user = $emailUser;
        } elseif (!empty($nicknameUser)) {
            $user = $nicknameUser;
        }

        if (!$user) {
            $errorInfo = $this->getServiceKernel()->trans('importer.org.check.repeat_row_info.row_user_data_error', array('%row%' => $row));
        }

        return array('errorInfo' => $errorInfo, 'user' => $user);
    }

    public function getTemplate(Request $request)
    {
        return $this->render('advanced-user-select/widgets/advanced-user-select-importer.html.twig', array(
            'importerType' => $this->type,
            'targetId' => $this->targetId,
            'targetType' => $this->targetType,
        ));
    }

    public function tryImport(Request $request)
    {
        $this->targetType = $request->request->get('targetType');
        $this->targetId = $request->request->get('targetId');
        $this->notificationSetting = $request->request->get('notificationSetting');

        $selector = $this->getMemberSelector($this->targetType);

        return $selector->canSelect($this->targetId);
    }

    protected function getMemberSelector($targetType)
    {
        $memberSelectFactory = $this->getServiceKernel()->getBiz()->offsetGet('advanced_member_select_factory');

        return $memberSelectFactory->create($targetType);
    }

    protected function getOfflineActivityService()
    {
        return $this->getServiceKernel()->getBiz()->service('CorporateTrainingBundle:OfflineActivity:OfflineActivityService');
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
