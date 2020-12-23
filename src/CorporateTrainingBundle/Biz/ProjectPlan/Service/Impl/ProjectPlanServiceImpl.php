<?php

namespace CorporateTrainingBundle\Biz\ProjectPlan\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Content\Service\FileService;
use Codeages\Biz\Framework\Event\Event;
use CorporateTrainingBundle\Biz\ProjectPlan\Dao\Impl\ProjectPlanDaoImpl;
use CorporateTrainingBundle\Biz\ProjectPlan\Dao\Impl\ProjectPlanItemDaoImpl;
use CorporateTrainingBundle\Biz\ProjectPlan\Service\ProjectPlanService;
use CorporateTrainingBundle\Biz\ResourceScope\Service\ResourceAccessScopeService;
use CorporateTrainingBundle\Biz\ResourceScope\Service\ResourceVisibleScopeService;

class ProjectPlanServiceImpl extends BaseService implements ProjectPlanService
{
    public function createProjectPlan($projectPlan)
    {
        if (!$this->hasManageProjectPlanPermission()) {
            throw $this->createAccessDeniedException('Access Denied');
        }

        $fields = $this->filterProjectPlanFields($projectPlan);

        if (!ArrayToolkit::requireds($fields, array('name', 'orgId', 'orgCode'))) {
            throw $this->createInvalidArgumentException('Lack of required fields');
        }

        $currentUser = $this->getCurrentUser();
        $fields['createdUserId'] = $currentUser['id'];

        return $this->getProjectPlanDao()->create($fields);
    }

    public function updateProjectPlan($id, $projectPlan)
    {
        if (!$this->canManageProjectPlan($id)) {
            throw $this->createAccessDeniedException('Access Denied');
        }

        $fields = $this->filterProjectPlanFields($projectPlan);

        return $this->getProjectPlanDao()->update($id, $fields);
    }

    public function deleteProjectPlan($id)
    {
        if (!$this->canManageProjectPlan($id)) {
            throw $this->createAccessDeniedException('Access Denied');
        }

        $projectPlan = $this->getProjectPlan($id);
        if (empty($projectPlan)) {
            throw $this->createNotFoundException('Training ProjectPlan Not Found');
        }

        if ('published' == $projectPlan['status']) {
            throw $this->createAccessDeniedException('Delete Published Training ProjectPlan Is Not Allowed');
        }

        try {
            $this->beginTransaction();

            $this->deleteItemByProjectPlanId($id);
            $this->getProjectPlanMemberService()->deleteMemberByProjectPlanId($id);
            $result = $this->getProjectPlanDao()->delete($id);

            $this->commit();

            return $result;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function getProjectPlan($id)
    {
        return $this->getProjectPlanDao()->get($id);
    }

    public function getMonthlyProjectPlansNumAndMembersNumByOrgAndCategory($date, $orgCode, $categoryId)
    {
        $projectPlanIds = $this->getProjectPlanDao()->findMonthlyProjectPlanIdsByOrgAndCategory($date, $orgCode, $categoryId);
        $projectPlansNum = count($projectPlanIds);
        $ids = ArrayToolkit::column($projectPlanIds, 'id');
        $projectPlanIds = empty($ids) ? array(-1) : $ids;
        $projectPlanMembersNum = $this->getProjectPlanMemberService()->countProjectPlanMembers(
            array(
                'projectPlanIds' => $projectPlanIds,
            )
        );

        return array($projectPlansNum, $projectPlanMembersNum);
    }

    public function findProjectPlansByIds($ids)
    {
        return $this->getProjectPlanDao()->findByIds($ids);
    }

    public function findProjectPlansByCreatedUserId($createdUserId)
    {
        return $this->getProjectPlanDao()->findByCreatedUserId($createdUserId);
    }

    public function findProjectPlanByCategoryId($categoryId)
    {
        return $this->getProjectPlanDao()->findByCategoryId($categoryId);
    }

    public function findUnfinishedProjectPlansByCurrentUserId($start, $limit)
    {
        $projectPlanIds = $this->getUnfinishedProjectPlanIds();
        $projectPlanIds = empty($projectPlanIds) ? array(-1) : $projectPlanIds;

        return $this->searchProjectPlans(array('ids' => $projectPlanIds, 'excludeStatus' => array('archived')), array(), $start, $limit);
    }

    public function findProjectPlansByDateAndIds($date, $projectIds, $start, $limit)
    {
        if (empty($projectIds)) {
            return array();
        }

        return $this->getProjectPlanDao()->findByDateAndIds($date, $projectIds, $start, $limit);
    }

    public function countProjectPlansByDateAndIds($date, $projectIds)
    {
        if (empty($projectIds)) {
            return 0;
        }

        return $this->getProjectPlanDao()->countByDateAndIds($date, $projectIds);
    }

    protected function getUnfinishedProjectPlanIds()
    {
        $userId = $this->getCurrentUser()->getId();
        $members = $this->getProjectPlanMemberService()->findMembersByUserId($userId);
        $projectPlanIds = !empty($members) ? ArrayToolkit::column($members, 'projectPlanId') : array();
        if (!empty($projectPlanIds)) {
            $projectPlans = $this->searchProjectPlans(array('ids' => $projectPlanIds, 'excludeStatus' => array('archived')), array(), 0, PHP_INT_MAX);
            $projectPlanIds = ArrayToolkit::column($projectPlans, 'id');
        }
        foreach ($projectPlanIds as $key => &$projectPlanId) {
            $personalProgress = $this->getPersonalProjectPlanProgress($projectPlanId, $userId);
            if (100 == $personalProgress) {
                unset($projectPlanIds[$key]);
            }
        }

        return $projectPlanIds;
    }

    public function countUnfinishedProjectPlansByCurrentUserId()
    {
        $projectPlanIds = $this->getUnfinishedProjectPlanIds();

        return count($projectPlanIds);
    }

    public function changeCover($id, $coverArray)
    {
        if (empty($coverArray)) {
            throw $this->createInvalidArgumentException('Invalid Param: cover');
        }
        $projectPlan = $this->getProjectPlan($id);
        $covers = array();
        foreach ($coverArray as $cover) {
            $file = $this->getFileService()->getFile($cover['id']);
            $covers[$cover['type']] = $file['uri'];
        }

        return $this->getProjectPlanDao()->update($projectPlan['id'], array('cover' => $covers));
    }

    public function countProjectPlans(array $conditions)
    {
        return $this->getProjectPlanDao()->count($conditions);
    }

    public function searchProjectPlans(array $conditions, array $orderBys, $start, $limit)
    {
        $conditions = $this->prepareSearchConditions($conditions);

        return $this->getProjectPlanDao()->search($conditions, $orderBys, $start, $limit);
    }

    public function publishProjectPlan($id)
    {
        if (!$this->canManageProjectPlan($id)) {
            throw $this->createAccessDeniedException('Access Denied');
        }

        $projectPlan = $this->getProjectPlan($id);
        if (empty($projectPlan)) {
            throw $this->createNotFoundException('Training ProjectPlan Not Found');
        }

        return $this->updateProjectPlan($id, array('status' => 'published'));
    }

    public function closeProjectPlan($id)
    {
        if (!$this->canManageProjectPlan($id)) {
            throw $this->createAccessDeniedException('Access Denied');
        }

        $projectPlan = $this->getProjectPlan($id);
        if (empty($projectPlan)) {
            throw $this->createNotFoundException('Training ProjectPlan Not Found');
        }

        return $this->updateProjectPlan($id, array('status' => 'closed'));
    }

    public function archiveProjectPlan($id)
    {
        if (!$this->canManageProjectPlan($id)) {
            throw $this->createAccessDeniedException('Access Denied');
        }

        $projectPlan = $this->getProjectPlan($id);
        if (empty($projectPlan)) {
            throw $this->createNotFoundException('Training ProjectPlan Not Found');
        }

        return $this->updateProjectPlan($id, array('status' => 'archived'));
    }

    public function applyAttendProjectPlan($recordId, $fields)
    {
        $enrollmentRecord = $this->getProjectPlanMemberService()->getEnrollmentRecord($recordId);
        if (empty($enrollmentRecord)) {
            throw $this->createNotFoundException('Enrollment Record Not Found');
        }
        if ($this->canApplyAttendProjectPlan($enrollmentRecord['projectPlanId'], $enrollmentRecord['userId'])) {
            $enrollmentRecord['remark'] = empty($fields['remark']) ? '' : $fields['remark'];
            $enrollmentRecord['submittedTime'] = time();
            $enrollmentRecord['status'] = 'submitted';

            return $this->getProjectPlanMemberService()->updateEnrollmentRecord($recordId, $enrollmentRecord);
        }

        return false;
    }

    public function canApplyAttendProjectPlan($projectPlanId, $userId)
    {
        $projectPlan = $this->getProjectPlan($projectPlanId);

        if (empty($projectPlan)) {
            return false;
        }

        if ('published' != $projectPlan['status']) {
            return false;
        }

        $user = $this->getUserService()->getUser($userId);
        if (empty($user)) {
            return false;
        }

        if ($projectPlan['conditionalAccess']) {
            if (!$this->getResourceAccessService()->canUserAccessResource('projectPlan', $projectPlan['id'], $userId)) {
                return false;
            }
        } else {
            if (!$this->getResourceVisibleScopeService()->canUserVisitResource('projectPlan', $projectPlan['id'], $userId)) {
                return false;
            }
        }

        if (!empty($projectPlan['enrollmentStartDate']) && $projectPlan['enrollmentStartDate'] > time()) {
            return false;
        }

        if (!empty($projectPlan['enrollmentEndDate']) && $projectPlan['enrollmentEndDate'] < time()) {
            return false;
        }

        $member = $this->getProjectPlanMemberService()->getProjectPlanMemberByUserIdAndProjectPlanId($userId, $projectPlanId);

        if (!empty($member)) {
            return false;
        }

        $memberCount = $this->getProjectPlanMemberService()->countProjectPlanMembers(array('projectPlanId' => $projectPlanId));
        if (!empty($projectPlan['maxStudentNum']) && $memberCount >= $projectPlan['maxStudentNum']) {
            return false;
        }

        $enrollmentRecord = $this->getProjectPlanMemberService()->searchEnrollmentRecords(array('projectPlanId' => $projectPlanId, 'status' => 'submitted', 'userId' => $userId), array(), 0, PHP_INT_MAX);

        if (!empty($enrollmentRecord)) {
            return false;
        }

        return true;
    }

    public function canUserVisitResource($projectPlanId)
    {
        $member = $this->getProjectPlanMemberService()->getProjectPlanMemberByUserIdAndProjectPlanId($this->getCurrentUser()->getId(), $projectPlanId);
        if (!empty($member)) {
            return true;
        }

        $canUserAccessResource = $this->getResourceVisibleService()->canUserVisitResource('projectPlan', $projectPlanId, $this->getCurrentUser()->getId());
        if ($canUserAccessResource) {
            return true;
        }

        return false;
    }

    public function getUserApplyStatus($projectPlanId, $userId)
    {
        $canAccess = true;
        $projectPlan = $this->getProjectPlan($projectPlanId);

        $member = $this->getProjectPlanMemberService()->getProjectPlanMemberByUserIdAndProjectPlanId($userId, $projectPlanId);
        if (!empty($member)) {
            return  'success';
        }

        if (empty($projectPlan['requireEnrollment'])) {
            return 'enrollmentUnOpen';
        }

        if (time() > $projectPlan['enrollmentEndDate']) {
            return 'enrollmentEnd';
        }

        if (time() < $projectPlan['enrollmentStartDate']) {
            return 'enrollmentUnStart';
        }

        if ($projectPlan['conditionalAccess']) {
            if (!$this->getResourceAccessService()->canUserAccessResource('projectPlan', $projectPlan['id'], $userId)) {
                return 'notAvailableForYou';
            }
        } else {
            if (!$this->getResourceVisibleScopeService()->canUserVisitResource('projectPlan', $projectPlan['id'], $userId)) {
                return 'notAvailableForYou';
            }
        }

        $memberCount = $this->getProjectPlanMemberService()->countProjectPlanMembers(array('projectPlanId' => $projectPlanId));

        $record = $this->getProjectPlanMemberService()->getLatestEnrollmentRecordByProjectPlanIdAndUserId($projectPlanId, $userId);

        if (!empty($record) && 'submitted' == $record['status']) {
            return $record['status'];
        }
        $rejectedRecord = $this->getProjectPlanMemberService()->searchEnrollmentRecords(array('status' => 'rejected', 'userId' => $userId, 'projectPlanId' => $projectPlanId), array('updatedTime' => 'DESC'), 0, 1);

        if ((!empty($rejectedRecord) && ($memberCount < $projectPlan['maxStudentNum'] || 0 == $projectPlan['maxStudentNum'])) && $canAccess) {
            return 'reset';
        }

        if ($memberCount < $projectPlan['maxStudentNum'] || 0 == $projectPlan['maxStudentNum']) {
            return 'enrollAble';
        }

        if ($memberCount >= $projectPlan['maxStudentNum']) {
            return 'enrollUnable';
        }
    }

    public function getProjectPlanProgress($projectPlanId)
    {
        $finishedItemNum = 0;
        $projectPlanItems = $this->findProjectPlanItemsByProjectPlanId($projectPlanId);
        foreach ($projectPlanItems as $projectPlanItem) {
            $strategy = $this->createProjectPlanStrategy($projectPlanItem['targetType']);
            $finishedMemberNum = $strategy->getFinishedMembersNum($projectPlanItem);
            $finishedItemNum += $finishedMemberNum;
        }
        $member = $this->getProjectPlanMemberService()->countProjectPlanMembers(array('projectPlanId' => $projectPlanId));
        $itemNum = $this->countProjectPlanItems(array('projectPlanId' => $projectPlanId));

        return (0 != $itemNum && 0 != $member) ? round(($finishedItemNum / ($itemNum * $member)) * 100, 0) : 0;
    }

    public function getPersonalProjectPlanProgress($projectPlanId, $userId)
    {
        $projectPlanItems = $this->findProjectPlanItemsByProjectPlanId($projectPlanId);
        $finishedItemNum = $this->getFinishedItemNum($projectPlanItems, $userId);
        $itemNum = count($projectPlanItems);

        return (0 != $itemNum) ? round(($finishedItemNum / $itemNum) * 100, 2) : 0;
    }

    public function getFinishedItemNum($projectPlanItems, $userId)
    {
        $finishedItemNum = 0;
        $user = $this->getUserService()->getUser($userId);
        foreach ($projectPlanItems as $projectPlanItem) {
            $strategy = $this->createProjectPlanStrategy($projectPlanItem['targetType']);
            $isFinished = $strategy->isFinished($projectPlanItem, $user);
            if ($isFinished) {
                ++$finishedItemNum;
            }
        }

        return $finishedItemNum;
    }

    public function appendProgress($projectPlanId, $members)
    {
        foreach ($members as &$member) {
            $member['progress'] = $this->getPersonalProjectPlanProgress($projectPlanId, $member['userId']);
        }

        return $members;
    }

    protected function prepareSearchConditions($conditions)
    {
        if (!empty($conditions['currentState']) && 'ongoing' === $conditions['currentState']) {
            $conditions['enrollmentEndDate_GE'] = time();
        }

        if (!empty($conditions['currentState']) && 'end' === $conditions['currentState']) {
            $conditions['enrollmentEndDate_LE'] = time();
        }

        if (!empty($conditions['currentState']) && 'all' === $conditions['currentState']) {
            unset($conditions['currentState']);
        }

        return $conditions;
    }

    public function createProjectPlanItem($item)
    {
        $fields = $this->filterProjectPlanItemFields($item);

        if (!$this->canManageProjectPlan($fields['projectPlanId'])) {
            throw $this->createAccessDeniedException('Access Denied');
        }

        if (!ArrayToolkit::requireds($fields, array('projectPlanId', 'targetType', 'targetId'))) {
            throw $this->createInvalidArgumentException('Lack of required fields');
        }

        if (!in_array($fields['targetType'], array('course', 'offline_course', 'exam', 'offline_exam'))) {
            throw $this->createInvalidArgumentException('Param Invalid: TargetType');
        }

        $item = $this->getProjectPlanItemDao()->create($fields);
        $this->waveProjectPlanItemNum($item['projectPlanId'], 1);

        return $item;
    }

    public function updateProjectPlanItem($id, $fields)
    {
        $item = $this->getProjectPlanItem($id);

        if (empty($item)) {
            $this->createNotFoundException("ProjectPlan#{$id} Not Found");
        }

        if (!$this->canManageProjectPlan($item['projectPlanId'])) {
            throw $this->createAccessDeniedException('Access Denied');
        }

        $fields = $this->filterProjectPlanItemFields($fields);

        return $this->getProjectPlanItemDao()->update($id, $fields);
    }

    public function updateProjectPlanItemTime($id, $fields)
    {
        $item = $this->getProjectPlanItem($id);

        if (empty($item)) {
            $this->createNotFoundException("ProjectPlan#{$id} Not Found");
        }

        $fields = ArrayToolkit::parts(
            $fields,
            array(
                'startTime',
                'endTime',
            ));

        return $this->getProjectPlanItemDao()->update($id, $fields);
    }

    public function updatePlanItem($id, $params, $type)
    {
        $strategy = $this->createProjectPlanStrategy($type);
        $strategy->updateItem($id, $params);
    }

    public function deleteProjectPlanItem($id)
    {
        $item = $this->getProjectPlanItem($id);

        if (empty($item)) {
            $this->createNotFoundException("ProjectPlan#{$id} Not Found");
        }

        if (!$this->canManageProjectPlan($item['projectPlanId'])) {
            throw $this->createAccessDeniedException('Access Denied');
        }

        $strategy = $this->createProjectPlanStrategy($item['targetType']);
        $strategy->deleteItem($item);
        $result = $this->getProjectPlanItemDao()->delete($id);
        $this->waveProjectPlanItemNum($item['projectPlanId'], '-1');

        return $result;
    }

    public function deleteItemByProjectPlanId($projectPlanId)
    {
        if (!$this->canManageProjectPlan($projectPlanId)) {
            throw $this->createAccessDeniedException('Access Denied');
        }

        $this->getProjectPlanItemDao()->deleteItemByProjectPlanId($projectPlanId);

        $items = $this->findProjectPlanItemsByProjectPlanId($projectPlanId);
        foreach ($items as &$item) {
            $this->deleteProjectPlanItem($item['id']);
        }

        return true;
    }

    public function getProjectPlanItem($id)
    {
        $item = $this->getProjectPlanItemDao()->get($id);
        $strategy = $this->createProjectPlanStrategy($item['targetType']);
        $item = $strategy->getItem($item);

        return $item;
    }

    public function findTaskDetailByTimeRangeAndProjectPlanId($startTime, $endTime, $projectPlanId)
    {
        $projectPlanItems = $this->findProjectPlanItemsByProjectPlanId($projectPlanId);
        foreach ($projectPlanItems as $key => $projectPlanItem) {
            $tasks = $this->findTasksByTimeRangeAndProjectPlanItem($startTime, $endTime, $projectPlanItem);
            if (!empty($tasks)) {
                foreach ($tasks as $task) {
                    if ($this) {
                        $reviewNum = $this->getReviewNum($task, $projectPlanItem['targetType']);
                    }
                    $detail[] = array(
                        'seq' => $key,
                        'id' => $task['id'],
                        'itemId' => $projectPlanItem['targetId'],
                        'itemType' => $projectPlanItem['targetType'],
                        'tagName' => (in_array($projectPlanItem['targetType'], array('exam', 'offline_exam'))) ? '考试' : '课程',
                        'type' => isset($task['type']) ? $task['type'] : '',
                        'title' => isset($task['title']) ? $task['title'] : $task['name'],
                        'place' => isset($task['place']) ? $task['place'] : '',
                        'hasHomework' => isset($task['hasHomework']) ? $task['hasHomework'] : '',
                        'homeworkDeadline' => isset($task['homeworkDeadline']) ? $task['homeworkDeadline'] : '',
                        'reviewNum' => isset($reviewNum) ? $reviewNum : '',
                        'startTime' => $task['startTime'],
                        'endTime' => $task['endTime'],
                    );
                }
            }
        }

        if (!empty($detail)) {
            $startTime = ArrayToolkit::column($detail, 'startTime');
            $seq = ArrayToolkit::column($detail, 'seq');
            array_multisort($startTime, SORT_ASC, $seq, SORT_ASC, $detail);
        }

        return !empty($detail) ? $detail : array();
    }

    protected function getReviewNum($task, $projectPlanItemType)
    {
        $strategy = $this->createProjectPlanStrategy($projectPlanItemType);
        $reviewNum = $strategy->getTaskReviewNum($task['id']);

        return $reviewNum;
    }

    protected function findTasksByTimeRangeAndProjectPlanItem($startTime, $endTime, $projectPlanItem)
    {
        $timeRange = array('startTime' => $startTime, 'endTime' => $endTime);
        $strategy = $this->createProjectPlanStrategy($projectPlanItem['targetType']);
        $tasks = $strategy->findTasksByItemIdAndTimeRange($projectPlanItem['targetId'], $timeRange);

        return $tasks;
    }

    public function getProjectPlanItemByTargetIdAndTargetType($targetId, $targetType)
    {
        return $this->getProjectPlanItemDao()->getByTargetIdAndTargetType($targetId, $targetType);
    }

    public function getProjectPlanItemByProjectPlanIdAndTargetIdAndTargetType($projectPlanId, $targetId, $targetType)
    {
        return $this->getProjectPlanItemDao()->getByProjectPlanIdAndTargetIdAndTargetType($projectPlanId, $targetId, $targetType);
    }

    public function findProjectPlanItemsByIds($ids)
    {
        $projectPlanItems = $this->getProjectPlanItemDao()->findByIds($ids);

        if (!$this->isPluginInstalled('Exam')) {
            foreach ($projectPlanItems as $key => $projectPlanItem) {
                if ('exam' == $projectPlanItem['targetType']) {
                    unset($projectPlanItems[$key]);
                }
            }
        }
        $projectPlanItems = $this->spliceItemDetail($projectPlanItems);

        return $projectPlanItems;
    }

    public function findProjectPlanItemsByProjectPlanId($projectPlanId)
    {
        $projectPlanItems = $this->getProjectPlanItemDao()->findByProjectPlanId($projectPlanId);

        if (!$this->isPluginInstalled('Exam')) {
            foreach ($projectPlanItems as $key => $projectPlanItem) {
                if ('exam' == $projectPlanItem['targetType']) {
                    unset($projectPlanItems[$key]);
                }
            }
        }
        $projectPlanItems = $this->spliceItemDetail($projectPlanItems);

        return $projectPlanItems;
    }

    public function findProjectPlanItemsByProjectPlanIds(array $projectPlanIds)
    {
        $projectPlanItems = $this->getProjectPlanItemDao()->findByProjectPlanIds($projectPlanIds);

        if (!$this->isPluginInstalled('Exam')) {
            foreach ($projectPlanItems as $key => $projectPlanItem) {
                if ('exam' == $projectPlanItem['targetType']) {
                    unset($projectPlanItems[$key]);
                }
            }
        }
        $projectPlanItems = $this->spliceItemDetail($projectPlanItems);

        return $projectPlanItems;
    }

    public function findHasFinishedSurveyResultProjectPlanItemIds($id, $userIds)
    {
        return $this->getProjectPlanItemDao()->findHasFinishedSurveyResultProjectPlanItemIds($id, $userIds);
    }

    public function findProjectPlanItemsByTargetIdAndTargetType($targetId, $targetType)
    {
        return $this->getProjectPlanItemDao()->findByTargetIdAndTargetType($targetId, $targetType);
    }

    public function countProjectPlanItems(array $conditions)
    {
        if (!$this->isPluginInstalled('Exam')) {
            $conditions['excludeTargetTypes'] = array('exam');
        }

        return $this->getProjectPlanItemDao()->count($conditions);
    }

    public function searchProjectPlanItems(array $conditions, array $orderBys, $start, $limit)
    {
        if (!$this->isPluginInstalled('Exam')) {
            $conditions['excludeTargetTypes'] = array('exam');
        }
        $projectPlanItems = $this->getProjectPlanItemDao()->search($conditions, $orderBys, $start, $limit);

        return $this->spliceItemDetail($projectPlanItems);
    }

    protected function spliceItemDetail($projectPlanItems)
    {
        $projectPlanItems = ArrayToolkit::index($projectPlanItems, 'id');
        $projectPlanItemsGroup = ArrayToolkit::group($projectPlanItems, 'targetType');
        foreach ($projectPlanItemsGroup as $key => $itemsGroup) {
            $strategy = $this->createProjectPlanStrategy($key);
            $itemsWithDetail = $strategy->findItemsDetail($itemsGroup);
            foreach ($itemsWithDetail as $key => $itemWithDetail) {
                if (isset($itemWithDetail['detail'])) {
                    $projectPlanItems[$key]['detail'] = $itemWithDetail['detail'];
                }
            }
        }

        return $projectPlanItems;
    }

    public function findProjectPlanItemsByProjectPlanIdAndTargetType($projectPlanId, $targetType)
    {
        return $this->getProjectPlanItemDao()->findByProjectPlanIdAndTargetType($projectPlanId, $targetType);
    }

    public function sortItems($ids)
    {
        foreach ($ids as $index => $id) {
            $this->getProjectPlanItemDao()->update($id, array('seq' => $index + 1));
        }
    }

    public function setProjectPlanItems($projectPlanId, $params, $type)
    {
        $strategy = $this->createProjectPlanStrategy($type);
        $strategy->createItems($projectPlanId, $params);

        $this->dispatchEvent('projectPlan.item.set', new Event($projectPlanId, array('params' => $params, 'type' => $type)));
        $this->getLogService()->info('projectPlan', 'set_projectPlan_items', 'add projectPlan items', $params);
    }

    public function createProjectPlanAdvancedOption($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('projectPlanId'))) {
            throw $this->createInvalidArgumentException('parameter is invalid!');
        }

        $fields = $this->filterAdvanceFields($fields);

        return $this->getProjectPlanAdvancedOptionDao()->create($fields);
    }

    public function updateProjectPlanAdvancedOption($id, $advancedOption)
    {
        if (!$this->canManageProjectPlan($advancedOption['projectPlanId'])) {
            throw $this->createAccessDeniedException('Access Denied');
        }

        $advancedOption = $this->filterAdvanceFields($advancedOption);

        return $this->getProjectPlanAdvancedOptionDao()->update($id, $advancedOption);
    }

    public function countProjectPlanAdvancedOptions($conditions)
    {
        return $this->getProjectPlanAdvancedOptionDao()->count($conditions);
    }

    public function getProjectPlanAdvancedOption($id)
    {
        return $this->getProjectPlanAdvancedOptionDao()->get($id);
    }

    public function searchProjectPlanAdvancedOptions(array $conditions, array $orderBys, $start, $limit)
    {
        return $this->getProjectPlanAdvancedOptionDao()->search($conditions, $orderBys, $start, $limit);
    }

    public function getProjectPlanAdvancedOptionByProjectPlanId($projectPlanId)
    {
        return $this->getProjectPlanAdvancedOptionDao()->getByProjectPlanId($projectPlanId);
    }

    public function waveProjectPlanItemNum($id, $num)
    {
        return $this->getProjectPlanDao()->wave(
            array($id),
            array('itemNum' => $num)
        );
    }

    protected function filterAdvanceFields($fields)
    {
        return ArrayToolkit::parts(
            $fields,
            array(
                'projectPlanId',
                'orgIds',
                'userGroupIds',
                'postIds',
                'requireRemark',
                'requireMaterial',
                'remarkRequirement',
                'materialRequirement',
                'material1',
                'material2',
                'material3',
            )
        );
    }

    protected function filterProjectPlanFields($fields)
    {
        return ArrayToolkit::parts(
            $fields,
            array(
                'name',
                'startTime',
                'endTime',
                'status',
                'summary',
                'createdUserId',
                'orgId',
                'orgCode',
                'cover',
                'itemNum',
                'memberNum',
                'maxStudentNum',
                'requireAudit',
                'enrollmentEndDate',
                'requireEnrollment',
                'enrollmentStartDate',
                'categoryId',
                'conditionalAccess',
                'showable',
            )
        );
    }

    protected function filterProjectPlanItemFields($fields)
    {
        return ArrayToolkit::parts(
            $fields,
            array(
                'projectPlanId',
                'targetType',
                'targetId',
                'seq',
                'startTime',
                'endTime',
            )
        );
    }

    protected function createProjectPlanStrategy($type)
    {
        return $this->biz->offsetGet('projectPlan_item_strategy_context')->createStrategy($type);
    }

    public function canManageProjectPlan($id)
    {
        $currentUser = $this->getCurrentUser();

        if (!$currentUser->isLogin()) {
            return false;
        }

        $projectPlan = $this->getProjectPlan($id);
        if (empty($projectPlan)) {
            return false;
        }

        if (!$currentUser->hasManagePermissionWithOrgCode($projectPlan['orgCode'])) {
            return false;
        }

        return $this->hasManageProjectPlanPermission();
    }

    public function hasManageProjectPlanPermission()
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser->isLogin()) {
            return false;
        }

        return $currentUser->hasPermission('admin_project_plan_manage');
    }

    protected function isPluginInstalled($pluginName)
    {
        return $this->biz['pluginConfigurationManager']->isPluginInstalled($pluginName);
    }

    /**
     * @return ProjectPlanDaoImpl
     */
    protected function getProjectPlanDao()
    {
        return $this->createDao('CorporateTrainingBundle:ProjectPlan:ProjectPlanDao');
    }

    /**
     * @return ProjectPlanItemDaoImpl
     */
    protected function getProjectPlanItemDao()
    {
        return $this->createDao('CorporateTrainingBundle:ProjectPlan:ProjectPlanItemDao');
    }

    /**
     * @return AdvancedOptionDao
     */
    protected function getProjectPlanAdvancedOptionDao()
    {
        return $this->createDao('CorporateTrainingBundle:ProjectPlan:AdvancedOptionDao');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\MemberService
     */
    protected function getProjectPlanMemberService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:MemberService');
    }

    /**
     * @return FileService
     */
    protected function getFileService()
    {
        return $this->createService('Content:FileService');
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    /**
     * @return ResourceVisibleScopeService
     */
    protected function getResourceVisibleService()
    {
        return $this->createService('CorporateTrainingBundle:ResourceScope:ResourceVisibleScopeService');
    }

    /**
     * @return ResourceAccessScopeService
     */
    protected function getResourceAccessService()
    {
        return $this->createService('CorporateTrainingBundle:ResourceScope:ResourceAccessScopeService');
    }

    /**
     * @return ResourceVisibleScopeService
     */
    protected function getResourceVisibleScopeService()
    {
        return $this->createService('ResourceScope:ResourceVisibleScopeService');
    }
}
