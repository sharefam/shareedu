<?php

namespace CorporateTrainingBundle\Biz\Importer;

use Symfony\Component\HttpFoundation\Request;
use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;
use Biz\Importer\Importer;

class UserGroupMemberImporter extends Importer
{
    protected $necessaryFields = array('nickname' => '用户名', 'verifiedMobile' => '手机', 'email' => '邮箱');
    protected $objWorksheet;
    protected $rowTotal = 0;
    protected $colTotal = 0;
    protected $excelFields = array();
    protected $passValidateUser = array();

    protected $type = 'user-group-member';

    public function import(Request $request)
    {
        $importData = $request->request->get('importData');
        $userGroupId = $request->request->get('userGroupId');
        $userGroup = $this->getUserGroupService()->getUserGroup($userGroupId);

        return $this->excelDataImporting($userGroup, $importData);
    }

    protected function excelDataImporting($userGroup, $userData)
    {
        $existsUserCount = 0;
        $successCount = 0;

        foreach ($userData as $key => $user) {
            if (!empty($user['nickname'])) {
                $user = $this->getUserService()->getUserByNickname($user['nickname']);
            } else {
                if (!empty($user['email'])) {
                    $user = $this->getUserService()->getUserByEmail($user['email']);
                } else {
                    $user = $this->getUserService()->getUserByVerifiedMobile($user['verifiedMobile']);
                }
            }

            $isUserGroupMember = $this->getUserGroupMemberService()->isMemberExistInUserGroup($userGroup['id'], $user['id'], 'user');

            if ($isUserGroupMember) {
                ++$existsUserCount;
            } else {
                if ($this->getUserGroupMemberService()->becomeMemberByImport($userGroup['id'], $user['id'])) {
                    ++$successCount;
                }

                $this->getLogService()->info('user-group', 'add_member', "用户组《{$userGroup['name']}》(#{$userGroup['id']})，添加用户{$user['nickname']}(#{$user['id']})，备注：通过批量导入添加");
            }
        }

        return array('existsUserCount' => $existsUserCount, 'successCount' => $successCount);
    }

    public function check(Request $request)
    {
        $file = $request->files->get('excel');
        $userGroupId = $request->request->get('userGroupId');

        $danger = $this->validateExcelFile($file);
        if (!empty($danger)) {
            return $danger;
        }

        $repeatInfo = $this->checkRepeatData();
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
                'userGroupId' => $userGroupId,
            )
        );
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
            empty($info) ? '' : $errorInfo[] = $info;

            $userCount = $userCount + 1;
            $allUserData[] = $userData;

            if (empty($errorInfo)) {
                if (!empty($userData['nickname'])) {
                    $user = $this->getUserService()->getUserByNickname($userData['nickname']);
                } elseif (!empty($userData['email'])) {
                    $user = $this->getUserService()->getUserByEmail($userData['email']);
                } elseif (!empty($userData['verifiedMobile'])) {
                    $user = $this->getUserService()->getUserByVerifiedMobile($userData['verifiedMobile']);
                }
                $validate[] = array_merge($user, array('row' => $row));
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
        } elseif (!empty($userData['email'])) {
            $user = $this->getUserService()->getUserByEmail($userData['email']);
        } elseif (!empty($userData['verifiedMobile'])) {
            $user = $this->getUserService()->getUserByVerifiedMobile($userData['verifiedMobile']);
        }

        if (!empty($user) && !empty($userData['email']) && !in_array($userData['email'], $user)) {
            $user = null;
        }

        if (!empty($user) && !empty($userData['verifiedMobile']) && !in_array($userData['verifiedMobile'], $user)) {
            $user = null;
        }

        if (!empty($user) && !empty($userData['nickname']) && !in_array($userData['nickname'], $user)) {
            $user = null;
        }

        if (!$user) {
            $errorInfo = $this->getServiceKernel()->trans('importer.check.repeat_row_info.row_data_error', array('%row%' => $row));
        }

        return $errorInfo;
    }

    public function getTemplate(Request $request)
    {
        $userGroupId = $request->query->get('userGroupId');
        $userGroup = $this->getUserGroupService()->getUserGroup($userGroupId);

        return $this->render(
            'admin/user-group-member/import.html.twig',
            array(
                'userGroup' => $userGroup,
                'importerType' => $this->type,
            )
        );
    }

    public function tryImport(Request $request)
    {
        $user = $this->biz['user'];
        $userGroupId = $request->query->get('userGroupId');

        if (empty($userGroupId)) {
            $userGroupId = $request->request->get('userGroupId');
        }

        if (!$user->isAdmin()) {
            throw new AccessDeniedException($this->getServiceKernel()->trans('importer.message.no_permission'));
        }
    }

    protected function getUserGroupService()
    {
        return $this->getServiceKernel()->getBiz()->service('CorporateTrainingBundle:UserGroup:UserGroupService');
    }

    protected function getUserGroupMemberService()
    {
        return $this->getServiceKernel()->getBiz()->service('CorporateTrainingBundle:UserGroup:MemberService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->getBiz()->service('User:UserService');
    }

    protected function getLogService()
    {
        return $this->getServiceKernel()->getBiz()->service('System:LogService');
    }
}
