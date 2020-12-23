<?php

namespace CorporateTrainingBundle\Biz\OfflineCourse\Service;

interface TaskService
{
    /**
     *  Task API.
     */
    public function createTask($task);

    public function updateTask($id, $fields);

    public function deleteTask($id);

    public function getTask($id);

    public function getTaskByActivityId($activityId);

    public function findTasksByIds($ids);

    public function findTasksByOfflineCourseId($offlineCourseId);

    public function findTasksByOfflineCourseIdAndTypeAndTimeRange($offlineCourseId, $type, $timeRange);

    public function searchTasks($conditions, $orderBys, $start, $limit);

    public function countTasks($conditions);

    public function sortTasks($ids);

    public function statisticsOfflineCourseTimeByTimeRangeAndCourseIds($timeRange, $courseIds);

    /**
     *  TaskResult API.
     */
    public function createTaskResult($taskResult);

    public function updateTaskResult($id, $fields);

    public function deleteTaskResult($id);

    public function deleteTaskResultByTaskId($taskId);

    public function getTaskResult($id);

    public function getTaskResultByTaskIdAndUserId($taskId, $userId);

    public function findTaskResultsByIds($ids);

    public function findTaskResultsByOfflineCourseId($offlineCourseId);

    public function findTaskResultsByUserId($userId);

    public function searchTaskResults($conditions, $orderBys, $start, $limit);

    public function countTaskResults($conditions);

    public function signIn($userId, $task);

    public function unattended($attendance);

    public function attended($attendance);

    public function findHomeworkStatusNumGroupByStatus($taskId, $userIds);

    public function passHomework($id);

    public function unpassHomework($id);

    public function getOfflineStudyHoursLearnDataForUserLearnDataExtension($conditions);
}
