<?php

namespace CorporateTrainingBundle\Biz\OfflineActivity\Service\Impl;

use Biz\BaseService;
use Biz\Content\Service\FileService;
use Biz\User\Service\UserService;
use Codeages\Biz\Framework\Event\Event;
use CorporateTrainingBundle\Biz\ManagePermission\Service\ManagePermissionOrgService;
use CorporateTrainingBundle\Biz\OfflineActivity\Dao\OfflineActivityDao;
use CorporateTrainingBundle\Biz\OfflineActivity\Service\EnrollmentRecordService;
use CorporateTrainingBundle\Biz\OfflineActivity\Service\MemberService;
use CorporateTrainingBundle\Biz\OfflineActivity\Service\OfflineActivityService;
use AppBundle\Common\ArrayToolkit;

class OfflineActivityServiceImpl extends BaseService implements OfflineActivityService
{
    public function findOfflineActivitiesByIds($ids)
    {
        return $this->getOfflineActivityDao()->findOfflineActivitiesByIds($ids);
    }

    public function createOfflineActivity($offlineActivity)
    {
        $this->validateOfflineActivityFields($offlineActivity);
        $offlineActivity = $this->filterOfflineActivityFields($offlineActivity);
        if (!$this->hasActivityCreateRole()) {
            throw $this->createAccessDeniedException('You have no access to Offline Activity Management');
        }
        $offlineActivity['status'] = 'draft';
        $offlineActivity['creator'] = $this->getCurrentUser()->getId();
        $offlineActivity = $this->filterNull($offlineActivity);

        return $this->getOfflineActivityDao()->create($offlineActivity);
    }

    public function searchOfflineActivities($conditions, $orderBy, $start, $limit)
    {
        $conditions = $this->prepareSearchConditions($conditions);

        return $this->getOfflineActivityDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function countOfflineActivities($conditions)
    {
        $conditions = $this->prepareSearchConditions($conditions);

        return $this->getOfflineActivityDao()->count($conditions);
    }

    public function getOfflineActivity($id)
    {
        return $this->getOfflineActivityDao()->get($id);
    }

    public function changeActivityCover($id, $coverArray)
    {
        if (empty($coverArray)) {
            throw $this->createInvalidArgumentException('Invalid Param: cover');
        }
        $offlineActivity = $this->getOfflineActivity($id);
        $covers = array();
        foreach ($coverArray as $cover) {
            $file = $this->getFileService()->getFile($cover['id']);
            $covers[$cover['type']] = $file['uri'];
        }

        $offlineActivity = $this->getOfflineActivityDao()->update($offlineActivity['id'], array('cover' => $covers));

        $this->getLogService()->info(
            'offlineActivity',
            'update_picture',
            "更新线下活动《{$offlineActivity['title']}》(#{$offlineActivity['id']})图片",
            $covers
        );

        return $offlineActivity;
    }

    public function updateOfflineActivity($id, $fields)
    {
        if (!ArrayToolkit::requireds($fields, array('title', 'startTime', 'endTime', 'address', 'maxStudentNum', 'enrollmentEndDate', 'enrollmentStartDate', 'orgId', 'orgCode'))) {
            throw $this->createInvalidArgumentException('Lack of required fields');
        }

        $offlineActivity = $this->getOfflineActivity($id);
        $fields = $this->fieldFields($fields);
        $fields = $this->filterNull($fields);

        if (isset($fields['summary'])) {
            $fields['summary'] = $this->purifyHtml($fields['summary'], true);
        }

        $offlineActivity = $this->getOfflineActivityDao()->update($offlineActivity['id'], $fields);
        $this->dispatchEvent('offline_activity.update', new Event($offlineActivity));

        $this->getLogService()->info('offlineActivity', 'update', "修改线下活动《{$offlineActivity['title']}》(#{$offlineActivity['id']})");

        return $offlineActivity;
    }

    public function applyAttendOfflineActivity($activityId, $userId)
    {
        if ($this->canApplyAttendOfflineActivity($activityId, $userId)) {
            $record = array(
                'offlineActivityId' => $activityId,
                'userId' => $userId,
                'submittedTime' => time(),
            );

            return $this->getEnrollmentRecordService()->createEnrollmentRecord($record);
        }
    }

    public function publishOfflineActivity($activityId)
    {
        $canManage = $this->canManageOfflineActivity($activityId);

        if (!$canManage) {
            throw $this->createServiceException('can not publish offline activity');
        }

        $offlineActivity = $this->getOfflineActivityDao()->update($activityId, array('status' => 'published'));
        $this->dispatchEvent('offline_activity.publish', new Event($offlineActivity));

        return $offlineActivity;
    }

    public function closeOfflineActivity($activityId)
    {
        if (!$this->canManageOfflineActivity($activityId)) {
            throw $this->createAccessDeniedException('You can not close offline activity ');
        }

        $offlineActivity = $this->getOfflineActivityDao()->get($activityId);
        if ('published' !== $offlineActivity['status']) {
            throw $this->createAccessDeniedException('$offlineActivity has not bean published');
        }
        $offlineActivity = $this->getOfflineActivityDao()->update($activityId, array('status' => 'closed'));

        $this->dispatchEvent('offline_activity.close', new Event($offlineActivity));

        return $offlineActivity;
    }

    public function getUserApplyStatus($activityId, $userId)
    {
        $member = $this->getMemberService()->getMemberByActivityIdAndUserId($activityId, $userId);
        if ('join' === $member['joinStatus']) {
            return 'join';
        }

        $activity = $this->getOfflineActivity($activityId);
        if (time() > $activity['enrollmentEndDate']) {
            return 'enrollmentEnd';
        }

        if ($activity['conditionalAccess']) {
            if (!$this->getResourceAccessService()->canUserAccessResource('offlineActivity', $activity['id'], $this->getCurrentUser()->getId())) {
                return 'notAvailableForYou';
            }
        } else {
            if (!$this->getResourceVisibleScopeService()->canUserVisitResource('offlineActivity', $activity['id'], $userId)) {
                return 'notAvailableForYou';
            }
        }

        $record = $this->getEnrollmentRecordService()->getLatestEnrollmentRecordByActivityIdAndUserId($activityId, $userId);
        if (!empty($record) && 'submitted' == $record['status']) {
            return $record['status'];
        }

        if ($activity['studentNum'] < $activity['maxStudentNum'] || 0 == $activity['maxStudentNum']) {
            return 'enrollAble';
        }

        if ($activity['studentNum'] >= $activity['maxStudentNum']) {
            return 'enrollUnable';
        }
    }

    public function hasActivityManageRole()
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            return false;
        }

        return $user->hasPermission('admin_offline_activity_manage') || $this->hasTrainingAdminRole();
    }

    public function hasActivityCreateRole()
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            return false;
        }

        return $user->hasPermission('admin_offline_activity_create') || $this->hasTrainingAdminRole();
    }

    public function hasTrainingAdminRole()
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser->isLogin()) {
            return false;
        }

        return in_array('ROLE_TRAINING_ADMIN', $currentUser['roles']);
    }

    public function refreshMemberCount($activityId)
    {
        $conditions = array(
            'activityId' => $activityId,
            'joinStatus' => 'join',
        );

        $count = $this->getMemberService()->countMembers($conditions);

        return $this->getOfflineActivityDao()->update($activityId, array('studentNum' => $count));
    }

    public function findOfflineActivitiesByCategoryId($categoryId)
    {
        return $this->getOfflineActivityDao()->findByCategoryId($categoryId);
    }

    public function canManageOfflineActivity($offlineActivityId)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            return false;
        }

        $offlineActivity = $this->getOfflineActivity($offlineActivityId);

        if (!$this->getCurrentUser()->hasManagePermissionWithOrgCode($offlineActivity['orgCode'])) {
            return false;
        }

        if ($user->hasPermission('admin_offline_activity_manage') || $this->hasTrainingAdminRole()) {
            return true;
        }

        return false;
    }

    protected function validateOfflineActivityFields($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('title', 'categoryId'))) {
            throw $this->createInvalidArgumentException('parameters is invalid');
        }
        $category = $this->getCategoryService()->getCategory($fields['categoryId']);
        if (empty($category)) {
            throw $this->createInvalidArgumentException('parameters is invalid');
        }
    }

    protected function filterOfflineActivityFields($fields)
    {
        return ArrayToolkit::parts(
            $fields,
            array(
                'title',
                'categoryId',
                'orgId',
                'orgCode',
            )
        );
    }

    protected function canApplyAttendOfflineActivity($activityId, $userId)
    {
        $activity = $this->getOfflineActivity($activityId);

        if (empty($activity)) {
            return false;
        }

        if ('published' != $activity['status']) {
            return false;
        }

        $user = $this->getUserService()->getUser($userId);
        if (empty($user)) {
            return false;
        }

        if ($activity['conditionalAccess']) {
            if (!$this->getResourceAccessService()->canUserAccessResource('offlineActivity', $activity['id'], $userId)) {
                return false;
            }
        } else {
            if (!$this->getResourceVisibleScopeService()->canUserVisitResource('offlineActivity', $activity['id'], $userId)) {
                return false;
            }
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

        $member = $this->getMemberService()->getMemberByActivityIdAndUserId($activityId, $userId);

        if (!empty($member) && 'join' === $member['joinStatus']) {
            return false;
        }

        return true;
    }

    public function canUserVisitResource($activityId)
    {
        $member = $this->getMemberService()->getMemberByActivityIdAndUserId($activityId, $this->getCurrentUser()->getId());
        if (!empty($member)) {
            return true;
        }

        $canUserAccessResource = $this->getResourceVisibleService()->canUserVisitResource('offlineActivity', $activityId, $this->getCurrentUser()->getId());
        if ($canUserAccessResource) {
            return true;
        }

        return false;
    }

    protected function prepareSearchConditions($conditions)
    {
        if (!empty($conditions['searchType']) && 'ongoing' === $conditions['searchType']) {
            $conditions['endTime_GE'] = time();
        }

        if (!empty($conditions['searchType']) && 'end' === $conditions['searchType']) {
            $conditions['endTime_LE'] = time();
        }

        if (!empty($conditions['searchType']) && 'all' === $conditions['searchType']) {
            unset($conditions['searchType']);
        }

        return $conditions;
    }

    /**
     * @return FileService
     */
    protected function getFileService()
    {
        return $this->createService('Content:FileService');
    }

    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    protected function fieldFields($fields)
    {
        $fields = ArrayToolkit::parts(
            $fields,
            array(
                'title',
                'categoryId',
                'startTime',
                'endTime',
                'address',
                'maxStudentNum',
                'enrollmentStartDate',
                'enrollmentEndDate',
                'summary',
                'requireAudit',
                'orgId',
                'orgCode',
                'conditionalAccess',
                'showable',
            )
        );

        if (!empty($fields['summary'])) {
            $fields['summary'] = $this->purifyHtml($fields['summary'], true);
        }

        return $fields;
    }

    protected function filterNull($fields)
    {
        return array_filter(
            $fields,
            function ($value) {
                if ('' === $value || null === $value) {
                    return false;
                }

                return true;
            }
        );
    }

    /**
     * @return MemberService
     */
    protected function getMemberService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineActivity:MemberService');
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
     * @return OfflineActivityDao
     */
    protected function getOfflineActivityDao()
    {
        return $this->createDao('CorporateTrainingBundle:OfflineActivity:OfflineActivityDao');
    }

    protected function getCategoryService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineActivity:CategoryService');
    }

    protected function getFileUsedDao()
    {
        return $this->createDao('File:FileUsedDao');
    }

    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }

    /**
     * @return ManagePermissionOrgService
     */
    protected function getManagePermissionOrgService()
    {
        return $this->createService('CorporateTrainingBundle:ManagePermission:ManagePermissionOrgService');
    }

    protected function getResourceVisibleService()
    {
        return $this->createService('ResourceScope:ResourceVisibleScopeService');
    }

    protected function getResourceAccessService()
    {
        return $this->createService('CorporateTrainingBundle:ResourceScope:ResourceAccessScopeService');
    }

    protected function getResourceVisibleScopeService()
    {
        return $this->createService('ResourceScope:ResourceVisibleScopeService');
    }
}
