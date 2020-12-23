<?php

namespace CorporateTrainingBundle\Biz\OfflineActivity\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\System\Service\LogService;
use Biz\User\Service\UserService;
use Codeages\Biz\Framework\Event\Event;
use CorporateTrainingBundle\Biz\OfflineActivity\Dao\MemberDao;
use CorporateTrainingBundle\Biz\OfflineActivity\Service\EnrollmentRecordService;
use CorporateTrainingBundle\Biz\OfflineActivity\Service\MemberService;
use CorporateTrainingBundle\Biz\OfflineActivity\Service\OfflineActivityService;

class MemberServiceImpl extends BaseService implements MemberService
{
    public function createMember($member)
    {
        if (!ArrayToolkit::requireds($member, array('offlineActivityId', 'userId'))) {
            throw $this->createServiceException('parameter is invalid!');
        }

        $member = $this->filterFields($member);

        $member = $this->getMemberDao()->create($member);
        $this->dispatchEvent(
            'offline.create.member',
            new Event($member)
        );

        return $member;
    }

    public function updateMember($id, $fields)
    {
        $fields = $this->filterFields($fields);

        return $this->getMemberDao()->update($id, $fields);
    }

    public function deleteMember($id)
    {
        $member = $this->getMember($id);
        if (empty($member)) {
            throw $this->createNotFoundException("member #{$id} not found");
        }

        $result = $this->getMemberDao()->delete($id);
        $this->dispatchEvent(
            'offline.member.delete',
            new Event($member)
        );

        return $result;
    }

    public function getMember($memberId)
    {
        return $this->getMemberDao()->get($memberId);
    }

    public function countMembers($conditions)
    {
        return $this->getMemberDao()->count($conditions);
    }

    public function searchMembers(array $conditions, array $orderBys, $start, $limit, $columns = array())
    {
        return $this->getMemberDao()->search($conditions, $orderBys, $start, $limit, $columns);
    }

    public function findDistinctUserIdsByDate($startTime, $endTime)
    {
        $endTime = strtotime(date('Y-m-d 23:59:59', $endTime));

        return $this->getMemberDao()->findDistinctUserIdsByCreatedTime($startTime, $endTime);
    }

    protected function filterFields($fields)
    {
        return ArrayToolkit::parts(
            $fields,
            array(
                'userId',
                'offlineActivityId',
                'attendedStatus',
                'score',
                'passedStatus',
                'evaluate',
                'joinStatus',
                'joinType',
            )
        );
    }

    public function findMembersByOfflineActivityId($activityId)
    {
        return $this->getMemberDao()->findByActivityId($activityId);
    }

    public function getMemberByActivityIdAndUserId($activityId, $userId)
    {
        return $this->getMemberDao()->getByActivityIdAndUserId($activityId, $userId);
    }

    public function becomeMember($activityId, $userId)
    {
        $canBecomeMember = $this->canBecomeMember($activityId, $userId);

        if (!$canBecomeMember) {
            throw $this->createServiceException('can not become member');
        }

        $member = $this->getMemberByActivityIdAndUserId($activityId, $userId);
        if (empty($member)) {
            $fields['joinStatus'] = 'join';
            $fields['offlineActivityId'] = $activityId;
            $fields['userId'] = $userId;

            $member = $this->createMember($fields);
        } else {
            $member = $this->updateMember($member['id'], array('joinStatus' => 'join'));
        }

        $this->getActivityService()->refreshMemberCount($activityId);

        return $member;
    }

    public function batchBecomeMember($activityId, $userIds)
    {
        if (!$this->getActivityService()->hasActivityManageRole()) {
            throw $this->createAccessDeniedException('Access Denied');
        }

        $activity = $this->getActivityService()->getOfflineActivity($activityId);
        if (empty($activity)) {
            throw $this->createNotFoundException("Activity #{$activityId} not found");
        }

        if ('published' != $activity['status']) {
            throw $this->createServiceException("Activity #{$activityId} not publish");
        }

        $existMembers = $this->findMembersByOfflineActivityId($activityId);
        $existMemberIds = ArrayToolkit::column($existMembers, 'userId');
        $beAddUserIds = array_diff($userIds, $existMemberIds);

        foreach ($beAddUserIds as $userId) {
            $fields['joinStatus'] = 'join';
            $fields['offlineActivityId'] = $activityId;
            $fields['userId'] = $userId;
            $this->createMember($fields);
        }

        $this->getActivityService()->refreshMemberCount($activityId);
        $this->dispatchEvent('offline_activity_batch_become_member', array('userIds' => $beAddUserIds, 'activity' => $activity));

        return true;
    }

    public function enter($activityId, $userId)
    {
        $member = $this->becomeMember($activityId, $userId);

        $this->dispatchEvent('offline.member.enter', new Event($member));

        return $member;
    }

    public function becomeMemberByImport($activityId, $userId)
    {
        if (!$this->getActivityService()->hasActivityManageRole()) {
            throw $this->createAccessDeniedException('Access Denied');
        }

        $activity = $this->getActivityService()->getOfflineActivity($activityId);
        if (empty($activity)) {
            throw $this->createNotFoundException("Activity #{$activityId} not found");
        }

        if ('published' != $activity['status']) {
            throw $this->createServiceException("Activity #{$activityId} not publish");
        }

        $user = $this->getUserService()->getUser($userId);

        if (empty($user)) {
            throw  $this->createNotFoundException("User #{$userId} not found");
        }

        $member = $this->getMemberByActivityIdAndUserId($activityId, $userId);
        if ($member) {
            throw  $this->createServiceException("Member #{$member['id']} has exist");
        }

        $record = $this->getEnrollmentRecordService()->getLatestEnrollmentRecordByActivityIdAndUserId($activityId, $userId);
        if (!empty($record) && 'submitted' == $record['status']) {
            $this->getEnrollmentRecordService()->updateEnrollmentRecord($record['id'], array('status' => 'approved'));
        }

        $fields['joinStatus'] = 'join';
        $fields['offlineActivityId'] = $activityId;
        $fields['userId'] = $userId;
        $fields['joinType'] = 'import';

        $this->getLogService()->info(
            'offlineActivity',
            'import_user',
            "import user {$userId} into activity {$activityId}"
        );

        $result = $this->createMember($fields);

        $this->getActivityService()->refreshMemberCount($activityId);

        return $result;
    }

    public function isMember($activityId, $userId)
    {
        $user = $this->getUserService()->getUser($userId);
        if (empty($user)) {
            return false;
        }

        $activity = $this->getActivityService()->getOfflineActivity($activityId);
        if (empty($activity)) {
            return false;
        }

        $member = $this->getMemberByActivityIdAndUserId($activityId, $userId);

        if (empty($member) || ('join' != $member['joinStatus'])) {
            return false;
        }

        return true;
    }

    public function attendMember($id, $attendedStatus)
    {
        $this->checkMemberExist($id);

        return $this->updateMember($id, array('attendedStatus' => $attendedStatus));
    }

    public function signIn($id)
    {
        return $this->attendMember($id, 'attended');
    }

    public function gradeMember($id, $fields)
    {
        $this->checkMemberExist($id);

        $fields = ArrayToolkit::parts($fields, array(
            'score',
            'passedStatus',
            'evaluate',
        ));

        if ($fields['score'] > 100 || $fields['score'] < 0) {
            throw $this->createServiceException('The score must between 0-100');
        }

        return $this->updateMember($id, $fields);
    }

    public function removeMember($id)
    {
        $member = $this->getMember($id);

        if (empty($member)) {
            throw $this->createNotFoundException("member #{$id} not found");
        }

        if (!$this->getActivityService()->hasActivityManageRole()) {
            throw $this->createAccessDeniedException('Access Denied');
        }

        $result = $this->getMemberDao()->update($id, array('joinStatus' => 'removed'));

        $this->getActivityService()->refreshMemberCount($member['offlineActivityId']);

        return $result;
    }

    public function statisticMembersAttendStatusByActivityId($activityId)
    {
        return $this->getMemberDao()->statisticAttendStatusByActivityId($activityId);
    }

    public function statisticMemberPassStatusByActivityId($activityId)
    {
        return $this->getMemberDao()->statisticPassStatusByActivityId($activityId);
    }

    public function statisticMemberScoreByActivityId($activityId)
    {
        return $this->getMemberDao()->statisticScoreByActivityId($activityId);
    }

    public function getOfflineActivityLearnDataForUserLearnDataExtension($conditions)
    {
        $usersActivityData = $this->getMemberDao()->calculateActivityDataByUserIdsAndDate($conditions['userIds'], $conditions['date']);

        $data = array();
        foreach ($usersActivityData as $userActivityData) {
            $data[$userActivityData['userId']] = $userActivityData['finishedOfflineActivityNum'].'/'.$userActivityData['learnedOfflineActivityNum'];
        }

        foreach ($conditions['userIds'] as $userId) {
            if (!isset($data[$userId])) {
                $data[$userId] = '0/0';
            }
        }

        return $data;
    }

    protected function checkMemberExist($id)
    {
        $member = $this->getMember($id);

        if (empty($member)) {
            throw $this->createNotFoundException("member #{$id} not found");
        }
    }

    protected function canBecomeMember($activityId, $userId)
    {
        $activity = $this->getActivityService()->getOfflineActivity($activityId);
        if (empty($activity) || 'published' !== $activity['status']) {
            return false;
        }

        $user = $this->getUserService()->getUser($userId);
        if (empty($user)) {
            return false;
        }

        if ($this->getCurrentUser()->hasPermission('admin_offline_activity_manage')) {
            return true;
        }

        if (time() > $activity['enrollmentEndDate']) {
            return false;
        }

        if (time() < $activity['enrollmentStartDate']) {
            return false;
        }

        if (!empty($activity['maxStudentNum']) && $activity['studentNum'] >= $activity['maxStudentNum']) {
            return false;
        }

        $member = $this->getMemberByActivityIdAndUserId($activityId, $userId);

        if (!empty($activity['requireAudit'])) {
            $latestEnrollmentRecord = $this->getEnrollmentRecordService()->getLatestEnrollmentRecordByActivityIdAndUserId(
                $activityId,
                $userId
            );

            if (empty($latestEnrollmentRecord) || 'approved' != $latestEnrollmentRecord['status']) {
                return false;
            }
        }

        if (empty($member) || 'removed' === $member['joinStatus']) {
            return true;
        }

        return false;
    }

    /**
     * @return OfflineActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineActivity:OfflineActivityService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return EnrollmentRecordService
     */
    protected function getEnrollmentRecordService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineActivity:EnrollmentRecordService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    /**
     * @return MemberDao
     */
    protected function getMemberDao()
    {
        return $this->createDao('CorporateTrainingBundle:OfflineActivity:MemberDao');
    }
}
