<?php

namespace CorporateTrainingBundle\Biz\Task\Service\Impl;

use CorporateTrainingBundle\Biz\Course\Service\CourseService;
use CorporateTrainingBundle\Biz\Task\Service\TaskService;
use AppBundle\Common\ArrayToolkit;
use Biz\Task\Service\Impl\TaskServiceImpl as BaseTaskServiceImpl;

class TaskServiceImpl extends BaseTaskServiceImpl implements TaskService
{
    public function findTaskByCourseIdAndTaskTypeAndTimeRange($courseId, $type, $time)
    {
        return $this->getTaskDao()->findByCourseIdAndTaskTypeAndTimeRange($courseId, $type, $time);
    }

    public function findTasksFetchActivityAndResultByCourseIdAndUserId($courseId, $userId)
    {
        $tasks = $this->findTasksFetchActivityByCourseId($courseId);
        if (empty($tasks)) {
            return array();
        }

        return $this->wrapUserTaskResultToTasks($userId, $courseId, $tasks);
    }

    public function getTaskByActivityId($activityId)
    {
        return $this->getTaskDao()->getByActivityId($activityId);
    }

    protected function wrapUserTaskResultToTasks($userId, $courseId, $tasks)
    {
        $taskIds = array_column($tasks, 'id');
        $taskResults = $this->getTaskResultService()->findUserTaskResultsByTaskIdsAndUserId($taskIds, $userId);
        $taskResults = ArrayToolkit::index($taskResults, 'courseTaskId');

        array_walk(
            $tasks,
            function (&$task) use ($taskResults) {
                $task['result'] = isset($taskResults[$task['id']]) ? $taskResults[$task['id']] : null;
            }
        );

        $user = $this->getUserService()->getUser($userId);
        $teacher = $this->getMemberService()->isCourseTeacher($courseId, $user['id']);

        $course = $this->getCourseService()->getCourse($courseId);
        $isLock = false;
        $magicSetting = $this->getSettingService()->get('magic');
        foreach ($tasks as &$task) {
            if ('freeMode' == $course['learnMode']) {
                $task['lock'] = false;
            } else {
                $task = $this->setTaskLockStatus($tasks, $task, $teacher);
            }

            //设置第一个发布的任务为解锁的
            if (!$isLock && 'published' === $task['status']) {
                $task['lock'] = false;
                $isLock = true;
            }

            //计算剩余观看时长
            $shouldCalcWatchLimitRemaining = !empty($magicSetting['lesson_watch_limit']) && 'video' == $task['type'] && 'self' == $task['mediaSource'] && $course['watchLimit'];
            if ($shouldCalcWatchLimitRemaining) {
                if ($task['result']) {
                    $task['watchLimitRemaining'] = $course['watchLimit'] * $task['length'] - $task['result']['watchTime'];
                } else {
                    $task['watchLimitRemaining'] = $course['watchLimit'] * $task['length'];
                }
            }

            $isTryLookable = $course['tryLookable'] && 'video' == $task['type'] && !empty($task['ext']['file']) && 'cloud' === $task['ext']['file']['storage'];
            if ($isTryLookable) {
                $task['tryLookable'] = 1;
            } else {
                $task['tryLookable'] = 0;
            }
        }

        return $tasks;
    }

    public function freshTaskLearnStat($taskId)
    {
        $userId = $this->getCurrentUser()->getId();
        $currentRecord = $this->getCurrentLearningTaskRecordService()->getCurrentLearningTaskRecordByUserId($userId);
        if (isset($currentRecord)) {
            $this->getCurrentLearningTaskRecordService()->updateCurrentLearningTaskRecord($currentRecord['id'], array('taskId' => $taskId, 'startTime' => time()));
        }

        if (empty($currentRecord)) {
            $currentRecord = array(
                'taskId' => $taskId,
                'userId' => $userId,
                'startTime' => time(),
                'lastTriggerTime' => time(),
            );
            $this->getCurrentLearningTaskRecordService()->createCurrentLearningTaskRecord($currentRecord);
        }
    }

    public function validTaskLearnStat($taskId, $data)
    {
        $userId = $this->getCurrentUser()->getId();
        $currentRecord = $this->getCurrentLearningTaskRecordService()->getCurrentLearningTaskRecordByUserId($userId);
        $now = time();

        if (empty($currentRecord)) {
            $currentRecord = array(
                'taskId' => $taskId,
                'userId' => $userId,
                'startTime' => time(),
                'lastTriggerTime' => time(),
            );

            $currentRecord = $this->getCurrentLearningTaskRecordService()->createCurrentLearningTaskRecord($currentRecord);
        }

        try {
            $this->beginTransaction();
            $currentRecord = $this->getCurrentLearningTaskRecordService()->getCurrentLearningTaskRecordForUpdate($currentRecord['id']);

            if ($currentRecord['taskId'] != $taskId) {
                $this->getCurrentLearningTaskRecordService()->updateCurrentLearningTaskRecord($currentRecord['id'], array('taskId' => $taskId));
                $this->commit();

                return false;
            }

            if (!empty($data['events'])) {
                $this->getCurrentLearningTaskRecordService()->updateCurrentLearningTaskRecord($currentRecord['id'], array('lastTriggerTime' => $now));
                $this->commit();

                return true;
            }

            //任务连续学习超过5小时则不再统计时长
            if ($now - $currentRecord['startTime'] > 60 * 60 * 5) {
                $this->commit();

                return false;
            }

            //任务每 $learnTimeSec 秒只允许触发一次，设定网络延迟周期-5秒作为标准判断
            $learnTimeSec = $this->getTimeSec('learn');
            if ($now - $currentRecord['lastTriggerTime'] < $learnTimeSec - 5) {
                $this->commit();

                return false;
            }

            $this->getCurrentLearningTaskRecordService()->updateCurrentLearningTaskRecord($currentRecord['id'], array('lastTriggerTime' => $now));

            $this->commit();

            return true;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * @return \CorporateTrainingBundle\Biz\CurrentLearningTaskRecord\Service\Impl\CurrentLearningTaskRecordServiceImpl
     */
    protected function getCurrentLearningTaskRecordService()
    {
        return $this->createService('CorporateTrainingBundle:CurrentLearningTaskRecord:CurrentLearningTaskRecordService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\Task\Dao\Impl\TaskDaoImpl
     */
    protected function getTaskDao()
    {
        return $this->createDao('CorporateTrainingBundle:Task:TaskDao');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('CorporateTrainingBundle:Course:CourseService');
    }

    /**
     * @return \Biz\User\Service\Impl\UserServiceImpl
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
