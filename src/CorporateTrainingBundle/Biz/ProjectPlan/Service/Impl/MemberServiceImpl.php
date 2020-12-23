<?php

namespace CorporateTrainingBundle\Biz\ProjectPlan\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use CorporateTrainingBundle\Biz\ProjectPlan\Service\MemberService;

class MemberServiceImpl extends BaseService implements MemberService
{
    /*
     *member
     */
    public function getProjectPlanMember($id)
    {
        return $this->getProjectPlanMemberDao()->get($id);
    }

    public function getProjectPlanMemberByUserIdAndProjectPlanId($userId, $projectPlanId)
    {
        return $this->getProjectPlanMemberDao()->getByUserIdAndProjectPlanId($userId, $projectPlanId);
    }

    public function findMembersByIds($ids)
    {
        return $this->getProjectPlanMemberDao()->findByIds($ids);
    }

    public function findMembersByUserId($userId)
    {
        return $this->getProjectPlanMemberDao()->findByUserId($userId);
    }

    public function findMembersByProjectPlanId($projectPlanId)
    {
        return $this->getProjectPlanMemberDao()->findByProjectPlanId($projectPlanId);
    }

    public function isMemberExit($userId, $projectPlanId)
    {
        $exist = false;
        $member = $this->getProjectPlanMemberByUserIdAndProjectPlanId($userId, $projectPlanId);
        if (!empty($member)) {
            $exist = true;
        }

        return $exist;
    }

    protected function createProjectPlanMember($member)
    {
        $fields = $this->filterProjectPlanMemberFields($member);

        if (!ArrayToolkit::requireds($fields, array('projectPlanId', 'userId'))) {
            throw $this->createInvalidArgumentException('Lack of required fields');
        }

        $currentUser = $this->getCurrentUser();
        $fields['operatedUserId'] = $currentUser['id'];
        $this->dispatchEvent('become_project_plan_member', $member);

        $member = $this->getProjectPlanMemberDao()->create($fields);

        return $member;
    }

    public function batchBecomeMember($projectPlanId, $userIds)
    {
        if (empty($projectPlanId) || empty($userIds)) {
            return;
        }

        $projectPlan = $this->getProjectPlanService()->getProjectPlan($projectPlanId);

        if (empty($projectPlan)) {
            throw $this->createNotFoundException();
        }

        if (!in_array($projectPlan['status'], array('published'))) {
            throw $this->createServiceException('Cannot join unpublished projectPlan');
        }

        $users = $this->getUserService()->searchUsers(
            array('userIds' => $userIds),
            array(),
            0,
            PHP_INT_MAX
        );
        $userIds = ArrayToolkit::column($users, 'id');
        $projectPlanMembers = $this->findMembersByProjectPlanId($projectPlanId);
        $existedUserIds = ArrayToolkit::column($projectPlanMembers, 'userId');

        $userIds = array_diff($userIds, $existedUserIds);

        foreach ($userIds as $userId) {
            $member = $this->getProjectPlanMemberByUserIdAndProjectPlanId($userId, $projectPlanId);
            if (empty($member)) {
                $fields = array(
                    'projectPlanId' => $projectPlanId,
                    'userId' => $userId,
                );
                $this->createProjectPlanMember($fields);
            }
        }

        return true;
    }

    public function updateProjectPlanMember($id, $fields)
    {
        $fields = $this->filterProjectPlanMemberFields($fields);

        return $this->getProjectPlanMemberDao()->update($id, $fields);
    }

    public function countProjectPlanMembers(array $conditions)
    {
        return $this->getProjectPlanMemberDao()->count($conditions);
    }

    public function searchProjectPlanMembers(array $conditions, array $orderBys, $start, $limit, $columns = array())
    {
        return $this->getProjectPlanMemberDao()->search($conditions, $orderBys, $start, $limit, $columns);
    }

    public function deleteProjectPlanMember($id)
    {
        $member = $this->getProjectPlanMember($id);

        if (empty($member)) {
            throw $this->createNotFoundException('ProjectPlan Member Not Found');
        }

        if (!$this->getProjectPlanService()->canManageProjectPlan($member['projectPlanId'])) {
            throw $this->createAccessDeniedException('Access Denied');
        }

        return $this->getProjectPlanMemberDao()->delete($id);
    }

    public function deleteMemberByProjectPlanId($projectPlanId)
    {
        if (!$this->getProjectPlanService()->canManageProjectPlan($projectPlanId)) {
            throw $this->createAccessDeniedException('Access Denied');
        }

        return $this->getProjectPlanMemberDao()->deleteMemberByProjectPlanId($projectPlanId);
    }

    public function isBelongToUserProjectPlan($userId, $itemId, $type)
    {
        $isBelong = false;

        if (empty($userId) || empty($itemId) || empty($type)) {
            return $isBelong;
        }

        if (!in_array($type, array('course'))) {
            return $isBelong;
        }

        $members = $this->findMembersByUserId($userId);
        if (empty($members)) {
            return $isBelong;
        }

        $projectPlanItems = $this->getProjectPlanService()->searchProjectPlanItems(
            array('targetType' => $type, 'targetId' => $itemId),
            array(),
            0,
            PHP_INT_MAX
        );

        if (empty($projectPlanItems)) {
            return $isBelong;
        }

        $projectPlanIds = ArrayToolkit::column($projectPlanItems, 'projectPlanId');

        $publishedProjectPlan = $this->getProjectPlanService()->searchProjectPlans(
            array('ids' => $projectPlanIds, 'status' => 'published'),
            array(),
            0,
            PHP_INT_MAX
        );

        $publishedProjectPlanIds = ArrayToolkit::column($publishedProjectPlan, 'id');

        $members = $this->searchProjectPlanMembers(
            array('projectPlanIds' => $publishedProjectPlanIds, 'userId' => $userId),
            array(),
            0,
            PHP_INT_MAX
        );

        if (!empty($members)) {
            $isBelong = true;
        }

        return $isBelong;
    }

    public function batchDeleteMembers($projectPlanId, $memberIds)
    {
        if (!$this->getProjectPlanService()->canManageProjectPlan($projectPlanId)) {
            throw $this->createAccessDeniedException('Access Denied');
        }

        foreach ($memberIds as $memberId) {
            $this->getProjectPlanMemberDao()->delete($memberId);
        }
    }

    public function getProjectPlanLearnDataForUserLearnDataExtension($conditions)
    {
        $usersProjectPlanData = $this->getProjectPlanMemberDao()->calculateProjectPlanLearnDataByUserIdsAndDate($conditions['userIds'], $conditions['date']);

        $data = array();
        foreach ($usersProjectPlanData as $userProjectPlanData) {
            $learnedProjectPlanIds = explode('|', $userProjectPlanData['learnedProjectPlanIds']);
            $learnedProjectPlanNum = $userProjectPlanData['learnedProjectPlanNum'];
            $finishedProjectPlanNum = $this->countFinishedProjectPlans($userProjectPlanData['userId'], $learnedProjectPlanIds);
            $data[$userProjectPlanData['userId']] = $finishedProjectPlanNum.'/'.$learnedProjectPlanNum;
        }

        foreach ($conditions['userIds'] as $userId) {
            if (!isset($data[$userId])) {
                $data[$userId] = '0/0';
            }
        }

        return $data;
    }

    protected function countFinishedProjectPlans($userId, $projectPlanIds)
    {
        $finishedProjectPlanNum = 0;
        foreach ($projectPlanIds as $projectPlanId) {
            $projectPlanItems = $this->getProjectPlanService()->findProjectPlanItemsByProjectPlanId($projectPlanId);
            $finishedItemNum = $this->getProjectPlanService()->getFinishedItemNum($projectPlanItems, $userId);
            $itemNum = count($projectPlanItems);

            if (0 != $itemNum && $finishedItemNum == $itemNum) {
                ++$finishedProjectPlanNum;
            }
        }

        return $finishedProjectPlanNum;
    }

    /*
     * EnrollmentRecord
     */
    public function attend($projectPlanId, $userId, $recordId, $fields)
    {
        $this->batchBecomeMember($projectPlanId, array($userId));
        $fields = array(
            'remark' => empty($fields['remark']) ? '' : $fields['remark'],
            'userId' => $userId,
            'projectPlanId' => $projectPlanId,
            'approvedTime' => time(),
            'submittedTime' => time(),
            'status' => 'approved',
        );

        $record = $this->updateEnrollmentRecord($recordId, $fields);

        $this->dispatchEvent('project_plan.attend.enrollment', $record);

        return $record;
    }

    public function createEnrollmentRecord($record)
    {
        if (!ArrayToolkit::requireds($record, array('userId', 'projectPlanId'))) {
            throw $this->createInvalidArgumentException('parameter is invalid!');
        }

        $record = $this->filterFields($record);

        return $this->getEnrollmentRecordDao()->create($record);
    }

    public function updateEnrollmentRecord($id, $fields)
    {
        $fields = $this->filterFields($fields);

        return $this->getEnrollmentRecordDao()->update($id, $fields);
    }

    public function getEnrollmentRecord($id)
    {
        return $this->getEnrollmentRecordDao()->get($id);
    }

    public function countEnrollmentRecords(array $conditions)
    {
        return $this->getEnrollmentRecordDao()->count($conditions);
    }

    public function searchEnrollmentRecords(array $conditions, array $orderBys, $start, $limit)
    {
        return $this->getEnrollmentRecordDao()->search($conditions, $orderBys, $start, $limit);
    }

    public function passProjectPlansApply($recordIds)
    {
        try {
            $this->biz['db']->beginTransaction();

            foreach ($recordIds as $recordId) {
                if (!$this->canAuditApply($recordId)) {
                    throw $this->createServiceException('can not audit this apply');
                }

                $record = $this->getEnrollmentRecord($recordId);
                if ('submitted' != $record['status']) {
                    throw $this->createServiceException('This apply cant not be approved');
                }

                $record = $this->updateEnrollmentRecord($recordId, array(
                    'status' => 'approved',
                    'approvedTime' => time(),
                ));

                $member = $this->getProjectPlanMemberByUserIdAndProjectPlanId($record['userId'], $record['projectPlanId']);
                if (empty($member)) {
                    $this->batchBecomeMember($record['projectPlanId'], array($record['userId']));
                }
            }

            $this->dispatchEvent('pass_project_plan_applies', $recordIds);
            $this->biz['db']->commit();
        } catch (\Exception $e) {
            $this->biz['db']->rollback();
            throw $e;
        }
    }

    public function rejectProjectPlansApply($recordIds, $info = array())
    {
        try {
            $this->biz['db']->beginTransaction();

            foreach ($recordIds as $recordId) {
                if (!$this->canAuditApply($recordId)) {
                    throw $this->createServiceException('can not audit this apply');
                }

                $record = $this->getEnrollmentRecord($recordId);
                if ('submitted' != $record['status']) {
                    throw $this->createServiceException('This apply can not be rejected');
                }

                $fields['status'] = 'rejected';
                if (!empty($info['rejectedReason'])) {
                    $fields['rejectedReason'] = $info['rejectedReason'];
                }

                $result = $this->updateEnrollmentRecord($recordId, $fields);
                $this->dispatchEvent('reject_project_plan_apply', $result);
            }

            $this->dispatchEvent('reject_project_plan_applies', $recordIds);

            $this->biz['db']->commit();
        } catch (\Exception $e) {
            $this->biz['db']->rollback();
            throw $e;
        }
    }

    public function findEnrollmentRecordsByProjectPlanId($projectPlanId)
    {
        return $this->getEnrollmentRecordDao()->findByProjectPlanId($projectPlanId);
    }

    public function findEnrollmentRecordsByIds($ids)
    {
        return $this->getEnrollmentRecordDao()->findByIds($ids);
    }

    public function getLatestEnrollmentRecordByProjectPlanIdAndUserId($projectPlanId, $userId)
    {
        return $this->getEnrollmentRecordDao()->getLatestEnrollmentRecordByProjectPlanIdAndUserId($projectPlanId, $userId);
    }

    protected function canAuditApply($recordId)
    {
        $record = $this->getEnrollmentRecord($recordId);
        if (empty($record)) {
            return false;
        }
        if (!$this->getProjectPlanService()->canManageProjectPlan($record['projectPlanId'])) {
            return false;
        }

        return true;
    }

    protected function filterFields($fields)
    {
        return ArrayToolkit::parts(
            $fields,
            array(
                'userId',
                'projectPlanId',
                'submittedTime',
                'status',
                'approvedTime',
                'rejectedReason',
                'remark',
            )
        );
    }

    protected function getEnrollmentRecordDao()
    {
        return $this->createDao('CorporateTrainingBundle:ProjectPlan:EnrollmentRecordDao');
    }

    protected function filterProjectPlanMemberFields($fields)
    {
        return ArrayToolkit::parts($fields, array('projectPlanId', 'userId', 'status', 'finishedTime'));
    }

    protected function createProjectPlanStrategy($type)
    {
        return $this->biz->offsetGet('projectPlan_item_strategy_context')->createStrategy($type);
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\Impl\ProjectPlanServiceImpl
     */
    protected function getProjectPlanService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:ProjectPlanService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Dao\Impl\MemberDaoImpl
     */
    protected function getProjectPlanMemberDao()
    {
        return $this->createDao('CorporateTrainingBundle:ProjectPlan:MemberDao');
    }

    /**
     * @return \Biz\User\Service\Impl\UserServiceImpl
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
