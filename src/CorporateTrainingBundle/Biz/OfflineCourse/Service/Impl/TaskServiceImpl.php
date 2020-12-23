<?php

namespace CorporateTrainingBundle\Biz\OfflineCourse\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Codeages\Biz\Framework\Event\Event;
use CorporateTrainingBundle\Biz\OfflineCourse\Service\TaskService;
use Biz\System\Service\LogService;

class TaskServiceImpl extends BaseService implements TaskService
{
    public function createTask($task)
    {
        $this->validateTaskFields($task);
        $activity = $this->createOfflineCourseActivity($task);
        $task['activityId'] = $activity['id'];
        $task['seq'] = $this->countTasks(array('offlineCourseId' => $task['offlineCourseId'])) + 1;
        $task = $this->filterTaskFields($task);
        $task = $this->getTaskDao()->create($task);

        $this->dispatchEvent('offline_course.task.create', $task);

        return $task;
    }

    public function updateTask($id, $fields)
    {
        $activityFields = array(
            'title' => $fields['title'],
        );
        if (!empty($fields['mediaId'])) {
            $activityFields['mediaId'] = $fields['mediaId'];
        }
        $this->updateActivity($fields['activityId'], $activityFields);

        $fields = $this->filterTaskFields($fields);
        $task = $this->getTaskDao()->update($id, $fields);
        $this->dispatchEvent('offline_course.task.update', $task);

        return $task;
    }

    public function deleteTask($id)
    {
        $task = $this->getTask($id);
        $this->deleteActivity($task['activityId']);
        $result = $this->getTaskDao()->delete($id);

        $this->dispatchEvent('offline_course.task.delete', $task);

        return $result;
    }

    public function getTask($id)
    {
        return $this->getTaskDao()->get($id);
    }

    public function getTaskByActivityId($activity)
    {
        return $this->getTaskDao()->getByActivityId($activity);
    }

    public function findTasksByIds($ids)
    {
        return $this->getTaskDao()->findByIds($ids);
    }

    public function findTasksByOfflineCourseId($offlineCourseId)
    {
        return $this->getTaskDao()->findByOfflineCourseId($offlineCourseId);
    }

    public function findTasksByOfflineCourseIdAndTypeAndTimeRange($offlineCourseId, $type, $timeRange)
    {
        return $this->getTaskDao()->findByOfflineCourseIdAndTypeAndTimeRange($offlineCourseId, $type, $timeRange);
    }

    public function searchTasks($conditions, $orderBys, $start, $limit)
    {
        return $this->getTaskDao()->search($conditions, $orderBys, $start, $limit);
    }

    public function countTasks($conditions)
    {
        return $this->getTaskDao()->count($conditions);
    }

    public function sortTasks($ids)
    {
        foreach ($ids as $index => $id) {
            $this->getTaskDao()->update($id, array('seq' => $index + 1));
        }
    }

    public function statisticsOfflineCourseTimeByTimeRangeAndCourseIds($timeRange, $courseIds)
    {
        $countStartTimeAndEndTimeAllInOfflineCourseTime = $this->getTaskDao()->countStartTimeAndEndTimeAllInOfflineCourseTime($timeRange,
            $courseIds);

        $countStartTimeEarlierThanSearchStartTimeOfflineCourseTime = $this->getTaskDao()->countStartTimeEarlierThanSearchStartTimeOfflineCourseTime($timeRange,
            $courseIds);

        $countEndTimeLaterThanSearchEndTimeOfflineCourseTime = $this->getTaskDao()->countEndTimeLaterThanSearchEndTimeOfflineCourseTime($timeRange,
            $courseIds);

        $countStartTimeAndEndTimeAllOutOfflineCourseTime = $this->getTaskDao()->countStartTimeAndEndTimeAllOutOfflineCourseTime($timeRange,
            $courseIds);

        return (isset($countStartTimeAndEndTimeAllInOfflineCourseTime[0]['offlineCourseTime']) ? $countStartTimeAndEndTimeAllInOfflineCourseTime[0]['offlineCourseTime'] : 0) + (isset($countStartTimeEarlierThanSearchStartTimeOfflineCourseTime[0]['offlineCourseTime']) ? $countStartTimeEarlierThanSearchStartTimeOfflineCourseTime[0]['offlineCourseTime'] : 0) + (isset($countEndTimeLaterThanSearchEndTimeOfflineCourseTime[0]['offlineCourseTime']) ? $countEndTimeLaterThanSearchEndTimeOfflineCourseTime[0]['offlineCourseTime'] : 0) + (isset($countStartTimeAndEndTimeAllOutOfflineCourseTime[0]['offlineCourseTime']) ? $countStartTimeAndEndTimeAllOutOfflineCourseTime[0]['offlineCourseTime'] : 0);
    }

    public function createTaskResult($taskResult)
    {
        $this->validateResultFields($taskResult);
        $taskResult = $this->filterResultFields($taskResult);

        return $this->getTaskResultDao()->create($taskResult);
    }

    public function updateTaskResult($id, $fields)
    {
        $fields = $this->filterResultFields($fields);

        return $this->getTaskResultDao()->update($id, $fields);
    }

    public function deleteTaskResult($id)
    {
        return $this->getTaskResultDao()->delete($id);
    }

    public function deleteTaskResultByTaskId($taskId)
    {
        return $this->getTaskResultDao()->deleteByTaskId($taskId);
    }

    public function getTaskResult($id)
    {
        return $this->getTaskResultDao()->get($id);
    }

    public function getTaskResultByTaskIdAndUserId($taskId, $userId)
    {
        return $this->getTaskResultDao()->getByTaskIdAndUserId($taskId, $userId);
    }

    public function findTaskResultsByIds($ids)
    {
        return $this->getTaskResultDao()->findByIds($ids);
    }

    public function findTaskResultsByOfflineCourseId($offlineCourseId)
    {
        return $this->getTaskResultDao()->findByOfflineCourseId($offlineCourseId);
    }

    public function findTaskResultsByUserId($userId)
    {
        return $this->getTaskResultDao()->findByUserId($userId);
    }

    public function searchTaskResults($conditions, $orderBys, $start, $limit)
    {
        return $this->getTaskResultDao()->search($conditions, $orderBys, $start, $limit);
    }

    public function countTaskResults($conditions)
    {
        return $this->getTaskResultDao()->count($conditions);
    }

    public function signIn($userId, $task)
    {
        $taskResult = $this->getTaskResultByTaskIdAndUserId($task['id'], $userId);
        if (empty($taskResult)) {
            $result = array(
                'offlineCourseId' => $task['offlineCourseId'],
                'userId' => $userId,
                'taskId' => $task['id'],
                'status' => (0 == $task['hasHomework']) ? 'finish' : 'start',
                'attendStatus' => ('questionnaire' == $task['type']) ? 'unattended' : 'attended',
                'homeworkStatus' => 'unsubmit',
                'finishedTime' => time(),
            );
            $taskResult = $this->createTaskResult($result);
            $member = $this->getOfflineCourseMemberService()->searchMembers(array(
                'offlineCourseId' => $task['offlineCourseId'],
                'userId' => $userId,
            ), array(), 0, PHP_INT_MAX);
            if (empty($member)) {
                $member = array(
                    'offlineCourseId' => $task['offlineCourseId'],
                    'userId' => $userId,
                    'learnedNum' => 0,
                );
                $this->getOfflineCourseMemberService()->createMember($member);
            }
        } else {
            $taskResult = $this->updateTaskResult($taskResult['id'], array('attendStatus' => 'attended'));
            if ('submitted' == $taskResult['homeworkStatus'] || 'passed' == $taskResult['homeworkStatus'] || 0 == $task['hasHomework']) {
                $this->updateTaskResult($taskResult['id'], array('status' => 'finish'));
                $this->updateFinishedTime($taskResult['id']);
            }
        }

        if ('offlineCourse' == $task['type']) {
            $this->dispatchEvent('project.plan.offline.course.signin', new Event($taskResult));
        }
    }

    public function attended($taskResult)
    {
        $offlineCourseMember = $this->getofflineCourseMemberService()->getMemberByOfflineCourseIdAndUserId($taskResult['offlineCourseId'],
            $taskResult['userId']);
        $task = $this->getTask($taskResult['taskId']);
        if (empty($offlineCourseMember)) {
            $this->getOfflineCourseMemberService()->createMember(array(
                'offlineCourseId' => $taskResult['offlineCourseId'],
                'userId' => $taskResult['userId'],
            ));
        }

        $oldTaskResult = $taskResult;
        $offlineCourse = $this->getOfflineCourseService()->tryManageOfflineCourse($taskResult['offlineCourseId']);

        if (empty($offlineCourse)) {
            throw $this->createNotFoundException();
        }

        $this->getLogService()->info('project_plan_offline_course', 'attend_user',
            "出勤《{$offlineCourse['title']}》(#{$taskResult['userId']})");

        $taskResult = $this->updateTaskResult($taskResult['id'], array('attendStatus' => 'attended'));
        if (0 == $task['hasHomework'] || 'submitted' == $taskResult['homeworkStatus'] || 'passed' == $taskResult['homeworkStatus']) {
            $this->updateTaskResult($taskResult['id'], array('status' => 'finish'));
            $taskResult = $this->updateFinishedTime($taskResult['id']);
        }

        $this->dispatchEvent('project.plan.offline.course.attended',
            new Event($taskResult, array('oldTaskResult' => $oldTaskResult)));
    }

    public function unattended($taskResult)
    {
        $offlineCourseMember = $this->getofflineCourseMemberService()->getMemberByOfflineCourseIdAndUserId($taskResult['offlineCourseId'],
            $taskResult['userId']);

        if (empty($offlineCourseMember)) {
            $this->getOfflineCourseMemberService()->createMember(array(
                'offlineCourseId' => $taskResult['offlineCourseId'],
                'userId' => $taskResult['userId'],
            ));
        }

        $oldTaskResult = $taskResult;
        $offlineCourse = $this->getOfflineCourseService()->tryManageOfflineCourse($taskResult['offlineCourseId']);

        if (empty($offlineCourse)) {
            throw $this->createNotFoundException();
        }

        $this->getLogService()->info('project_plan_offline_course', 'unattend_user',
            "未出勤《{$offlineCourse['title']}》(#{$taskResult['userId']})");

        $taskResult = $this->updateTaskResult($taskResult['id'],
            array('attendStatus' => 'unattended', 'status' => 'start'));

        $this->dispatchEvent('project.plan.offline.course.unattended',
            new Event($taskResult, array('oldTaskResult' => $oldTaskResult)));
    }

    public function findHomeworkStatusNumGroupByStatus($taskId, $userIds)
    {
        return $this->getTaskResultDao()->findHomeworkStatusNumGroupByStatus($taskId, $userIds);
    }

    public function passHomework($id)
    {
        $oldTaskResult = $this->getTaskResult($id);
        $offlineCourse = $this->getOfflineCourseService()->getOfflineCourse($oldTaskResult['offlineCourseId']);

        if (empty($offlineCourse)) {
            throw $this->createNotFoundException();
        }

        $this->getLogService()->info('project_plan_offline_course', 'pass_user_homework',
            "批阅作业通过《{$offlineCourse['title']}》(#{$oldTaskResult['userId']})");

        $taskResult = $this->updateTaskResult($oldTaskResult['id'], array('homeworkStatus' => 'passed'));
        if ('attended' == $taskResult['attendStatus']) {
            $this->updateTaskResult($taskResult['id'], array('status' => 'finish'));
        }

        $this->dispatchEvent('project.plan.offline.course.pass.homework',
            new Event($taskResult, array('oldTaskResult' => $oldTaskResult)));
    }

    public function unpassHomework($id)
    {
        $oldTaskResult = $this->getTaskResult($id);
        $offlineCourse = $this->getOfflineCourseService()->tryManageOfflineCourse($oldTaskResult['offlineCourseId']);

        if (empty($offlineCourse)) {
            throw $this->createNotFoundException();
        }

        $this->getLogService()->info('project_plan_offline_course', 'unpass_user_homework',
            "批阅作业通过《{$offlineCourse['title']}》(#{$oldTaskResult['userId']})");

        $taskResult = $this->updateTaskResult($oldTaskResult['id'],
            array('homeworkStatus' => 'unpassed', 'status' => 'start'));

        $this->dispatchEvent('project.plan.offline.course.unpass.homework',
            new Event($taskResult, array('oldTaskResult' => $oldTaskResult)));
    }

    protected function updateFinishedTime($taskResultId)
    {
        $taskResult = $this->updateTaskResult($taskResultId, array('finishedTime' => time()));

        return $taskResult;
    }

    public function findTasksFetchActivityByOfflineCourseId($id)
    {
        $tasks = $this->findTasksByOfflineCourseId($id);
        $activityIds = ArrayToolkit::column($tasks, 'activityId');
        $activities = $this->getActivityService()->findActivities($activityIds, true);
        $activities = ArrayToolkit::index($activities, 'id');

        array_walk(
            $tasks,
            function (&$task) use ($activities) {
                $task['activity'] = $activities[$task['activityId']];
            }
        );

        return $tasks;
    }

    public function submitHomework($taskResult, $attachment)
    {
        $offlineCourse = $this->getOfflineCourseService()->getOfflineCourse($taskResult['offlineCourseId']);

        $this->uploadMaterialAttachments($attachment, $taskResult['id']);
        $newTaskResult = $this->updateTaskResult($taskResult['id'], array('homeworkStatus' => 'submitted'));
        if ('attended' == $taskResult['attendStatus']) {
            $this->updateFinishedTime($taskResult['id']);
        }

        $this->dispatchEvent('offline.course.submit.homework', $newTaskResult);
    }

    protected function uploadMaterialAttachments($attachment, $taskResultId)
    {
        $file = $this->getUploadFileService()->findUseFilesByTargetTypeAndTargetIdAndType('projectPlaning.offline.homework',
            $taskResultId, 'attachment');

        if (!empty($attachment) && empty($file)) {
            $user = $this->getCurrentUser();
            $orgs = $this->getOrgService()->findOrgsByIds($user['orgIds']);
            $org = reset($orgs);
            $userProfile = $this->getUserService()->getUserProfile($user['id']);
            $orgName = empty($org) ? '' : $org['name'].'+';
            $trueName = empty($userProfile['truename']) ? '' : $userProfile['truename'].'+';
            $mobile = empty($userProfile['mobile']) ? '' : $userProfile['mobile'].'+';

            $file = $this->getUploadFileService()->getFile($attachment['fileIds']);
            $filename = $orgName.$trueName.$mobile.$file['filename'];
            $this->getUploadFileService()->update($attachment['fileIds'], array('name' => $filename));
            $this->getUploadFileService()->createUseFiles($attachment['fileIds'], $taskResultId,
                $attachment['targetType'], $attachment['type']);
        }
    }

    public function createOfflineCourseActivity($task)
    {
        $fields = array(
            'title' => $task['title'],
            'remark' => '',
            'mediaId' => empty($task['mediaId']) ? $task['offlineCourseId'] : $task['mediaId'],
            'mediaType' => $task['mediaType'],
            'type' => $task['mediaType'],
            'content' => '',
            'length' => 0,
            'fromCourseId' => $task['offlineCourseId'],
            'fromCourseSetId' => $task['offlineCourseId'],
            'fromUserId' => $task['creator'],
            'startTime' => empty($task['startTime']) ? 0 : $task['startTime'],
            'endTime' => empty($task['endTime']) ? 0 : $task['endTime'],
            'copyId' => '',
        );

        return $this->createActivity($fields);
    }

    public function getOfflineStudyHoursLearnDataForUserLearnDataExtension($conditions)
    {
        $usersOfflineLearnTime = $this->calculateUsersOfflineLearnTime($conditions);
        $usersOfflineLearnHours = array();

        foreach ($usersOfflineLearnTime as $key => $userOfflineLearnTime) {
            $usersOfflineLearnHours[$key] = round(($userOfflineLearnTime / 3600), 1);
        }

        return $usersOfflineLearnHours;
    }

    protected function calculateUsersOfflineLearnTime($conditions)
    {
        $offlineCourseIds = $this->getOfflineCourseService()->findPublishedCourseByUserIds($conditions['userIds']);
        $offlineCourseIds = ArrayToolkit::column($offlineCourseIds, 'id');
        if (empty($offlineCourseIds)) {
            return array();
        }

        $usersOfflineLearnTime = $this->getTaskResultDao()->calculateUsersOfflineLearnTime($conditions['userIds'],
            $offlineCourseIds, $conditions['date']);
        $usersOfflineLearnTime = ArrayToolkit::index($usersOfflineLearnTime, 'userId');

        $usersOfflineLearnTimeData = array();
        foreach ($conditions['userIds'] as $userId) {
            $usersOfflineLearnTimeData[$userId] = isset($usersOfflineLearnTime[$userId]['offlineStudyTime']) ? $usersOfflineLearnTime[$userId]['offlineStudyTime'] : 0;
        }

        return $usersOfflineLearnTimeData;
    }

    protected function createActivity($fields)
    {
        $activityConfig = $this->getActivityConfig($fields['mediaType']);

        $media = $activityConfig->create($fields);

        if (!empty($media)) {
            $fields['mediaId'] = $media['id'];
        }

        $fields['fromUserId'] = $this->getCurrentUser()->getId();
        $fields = $this->filterActivityFields($fields);
        $fields['createdTime'] = time();

        $activity = $this->getActivityDao()->create($fields);

        $listener = $activityConfig->getListener('activity.created');
        if (!empty($listener)) {
            $listener->handle($activity, array());
        }

        return $activity;
    }

    protected function updateActivity($id, $fields)
    {
        $savedActivity = $this->getActivityService()->getActivity($id);

        $realActivity = $this->getActivityConfig($savedActivity['mediaType']);

        $materials = $this->getActivityService()->getMaterialsFromActivity($fields, $realActivity);

        if (!empty($savedActivity['mediaId'])) {
            $media = $realActivity->update($savedActivity['mediaId'], $fields, $savedActivity);

            if (!empty($media)) {
                $fields['mediaId'] = $media['id'];
            }
        }

        $fields = $this->filterActivityFields($fields);

        return $this->getActivityDao()->update($id, $fields);
    }

    protected function deleteActivity($id)
    {
        $activity = $this->getActivityService()->getActivity($id);

        try {
            $this->beginTransaction();

            $activityConfig = $this->getActivityConfig($activity['mediaType']);
            $activityConfig->delete($activity['mediaId']);
            $this->getActivityLearnLogService()->deleteLearnLogsByActivityId($id);
            $result = $this->getActivityDao()->delete($id);
            $this->commit();

            return $result;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    protected function getActivityConfig($type)
    {
        return $this->biz["activity_type.{$type}"];
    }

    protected function filterActivityFields($fields)
    {
        $fields = ArrayToolkit::parts(
            $fields,
            array(
                'title',
                'remark',
                'mediaId',
                'mediaType',
                'content',
                'length',
                'fromCourseId',
                'fromCourseSetId',
                'fromUserId',
                'startTime',
                'endTime',
            )
        );

        return $fields;
    }

    protected function validateTaskFields($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('title', 'offlineCourseId'), true)) {
            throw $this->createInvalidArgumentException('Lack of required fields');
        }
    }

    protected function filterTaskFields($fields)
    {
        return ArrayToolkit::parts(
            $fields,
            array(
                'offlineCourseId',
                'title',
                'seq',
                'place',
                'hasHomework',
                'homeworkDeadline',
                'homeworkDemand',
                'creator',
                'orgId',
                'startTime',
                'endTime',
                'activityId',
                'type',
            )
        );
    }

    protected function validateResultFields($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('offlineCourseId', 'userId', 'taskId'), true)) {
            throw $this->createInvalidArgumentException('Lack of required fields');
        }
    }

    protected function filterResultFields($fields)
    {
        return ArrayToolkit::parts(
            $fields,
            array(
                'offlineCourseId',
                'userId',
                'taskId',
                'status',
                'attendStatus',
                'homeworkStatus',
                'finishedTime',
            )
        );
    }

    /**
     * @return \Biz\Activity\Service\Impl\ActivityLearnLogServiceImpl
     */
    protected function getActivityLearnLogService()
    {
        return $this->createService('Activity:ActivityLearnLogService');
    }

    /**
     * @return \Biz\File\Service\Impl\UploadFileServiceImpl
     */
    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineCourse\Dao\Impl\TaskDaoImpl
     */
    protected function getTaskDao()
    {
        return $this->createDao('CorporateTrainingBundle:OfflineCourse:TaskDao');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineCourse\Dao\Impl\TaskResultDaoImpl
     */
    protected function getTaskResultDao()
    {
        return $this->createDao('CorporateTrainingBundle:OfflineCourse:TaskResultDao');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineCourse\Service\Impl\OfflineCourseServiceImpl
     */
    protected function getOfflineCourseService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineCourse:OfflineCourseService');
    }

    /**
     * @return \Biz\Activity\Service\Impl\ActivityServiceImpl
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    /**
     * @return \Biz\Activity\Dao\Impl\ActivityDaoImpl
     */
    protected function getActivityDao()
    {
        return $this->createDao('Activity:ActivityDao');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineCourse\Service\Impl\MemberServiceImpl
     */
    protected function getOfflineCourseMemberService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineCourse:MemberService');
    }

    protected function getOrgService()
    {
        return $this->createService('Org:OrgService');
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
