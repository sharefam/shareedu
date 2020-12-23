<?php

namespace CorporateTrainingBundle\Biz\Task\Service\Impl;

use Biz\Task\Service\Impl\TaskResultServiceImpl as BaseTaskResultServiceImpl;
use Codeages\Biz\Framework\Event\Event;
use CorporateTrainingBundle\Biz\Task\Service\TaskResultService;

class TaskResultServiceImpl extends BaseTaskResultServiceImpl implements TaskResultService
{
    public function getTaskResultById($id)
    {
        return $this->getTaskResultDao()->get($id);
    }

    public function updateTaskResult($id, $taskResult)
    {
        return $this->getTaskResultDao()->update($id, $taskResult);
    }

    public function getTaskResultByTaskIdAndUserId($taskId, $userId)
    {
        return $this->getTaskResultDao()->getByTaskIdAndUserId($taskId, $userId);
    }

    public function sumLearnTimeByPostIdAndUserId($postId, $userId)
    {
        return $this->getTaskResultDao()->sumLearnTimeByPostIdAndUserId($postId, $userId);
    }

    public function sumLearnTimeByUserId($userId)
    {
        return $this->getTaskResultDao()->sumLearnTimeByUserId($userId);
    }

    public function sumLearnTimeByTaskIdAndUserId($taskId, $userId)
    {
        return $this->getTaskResultDao()->sumLearnTimeByTaskIdAndUserId($taskId, $userId);
    }

    public function sumLearnTimeByCategoryIdAndUserId($categoryId, $userId)
    {
        return $this->getTaskResultDao()->sumLearnTimeBycategoryIdAndUserId($categoryId, $userId);
    }

    public function sumWatchTimeByCourseIdAndUserId($courseId, $userId)
    {
        return $this->getTaskResultDao()->sumWatchTimeByCourseIdAndUserId($courseId, $userId);
    }

    public function sumWatchTimeByTaskIdAndUserId($taskId, $userId)
    {
        return $this->getTaskResultDao()->sumWatchTimeByTaskIdAndUserId($taskId, $userId);
    }

    public function sumWatchTimeByPostIdAndUserId($postId, $userId)
    {
        return $this->getTaskResultDao()->sumWatchTimeByPostIdAndUserId($postId, $userId);
    }

    public function sumWatchTimeByCategoryIdAndUserId($categoryId, $userId)
    {
        return $this->getTaskResultDao()->sumWatchTimeByCategoryIdAndUserId($categoryId, $userId);
    }

    public function waveLearnTime($id, $time)
    {
        $result = $this->getTaskResultDao()->wave([$id], [
            'time' => $time,
        ]);

        $this->dispatchEvent('wave.learn.time', new Event($id, ['learnTime' => $time]));

        return $result;
    }

    public function sumLearnTimeByCourseIdsAndUserIdsGroupByUserId(array $courseIds, array $userIds)
    {
        return $this->getTaskResultDao()->sumLearnTimeByCourseIdsAndUserIdsGroupByUserId($courseIds, $userIds);
    }

    public function sumLearnTimeByCourseId($courseId)
    {
        return $this->getTaskResultDao()->sumLearnTimeByCourseId($courseId);
    }

    public function sumCompulsoryTasksLearnTimeByCourseIdAndUserId($courseId, $userId)
    {
        return $this->getTaskResultDao()->sumCompulsoryTasksLearnTimeByCourseIdAndUserId($courseId, $userId);
    }

    public function sumCompulsoryTasksLearnTimeByCourseIdAndUserIds($courseId, $userIds)
    {
        return $this->getTaskResultDao()->sumCompulsoryTasksLearnTimeByCourseIdAndUserIds($courseId, $userIds);
    }

    public function countCompulsoryTaskResultsByCourseIdAndUserIds($courseId, $userIds)
    {
        return $this->getTaskResultDao()->countCompulsoryTaskResultsByCourseIdAndUserIds($courseId, $userIds);
    }

    public function findUserTaskResultsByTaskIdsAndUserId($taskIds, $userId)
    {
        $user = $this->getUserService()->getUser($userId);

        if (empty($user) || empty($taskIds)) {
            return [];
        }

        return $this->getTaskResultDao()->findByTaskIdsAndUserId($taskIds, $userId);
    }

    /**
     * @return \Biz\User\Service\Impl\UserServiceImpl
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
