<?php

namespace CorporateTrainingBundle\Biz\Task\Dao;

interface TaskResultDao
{
    public function sumLearnTimeByPostIdAndUserId($postId, $userId);

    public function sumLearnTimeByUserId($userId);

    public function sumLearnTimeByTaskIdAndUserId($taskId, $userId);

    public function sumLearnTimeByCategoryIdAndUserId($categoryId, $userId);

    public function sumWatchTimeByCourseIdAndUserId($courseId, $userId);

    public function sumWatchTimeByPostIdAndUserId($postId, $userId);

    public function sumWatchTimeByTaskIdAndUserId($taskId, $userId);

    public function sumWatchTimeByCategoryIdAndUserId($categoryId, $userId);

    public function sumLearnTimeByCourseIdsAndUserIdsGroupByUserId(array $courseIds, array $userIds);

    public function sumLearnTimeByCourseId($courseId);

    public function sumCompulsoryTasksLearnTimeByCourseIdAndUserId($courseId, $userId);

    public function countCompulsoryTaskResultsByCourseIdAndUserIds($courseId, $userIds);
}
