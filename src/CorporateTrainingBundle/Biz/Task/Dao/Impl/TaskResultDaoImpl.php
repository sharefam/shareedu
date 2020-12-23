<?php

namespace CorporateTrainingBundle\Biz\Task\Dao\Impl;

use Biz\Task\Dao\Impl\TaskResultDaoImpl as BaseDaoImpl;
use CorporateTrainingBundle\Biz\Task\Dao\TaskResultDao;

class TaskResultDaoImpl extends BaseDaoImpl implements TaskResultDao
{
    public function sumLearnTimeByCourseIdAndUserId($courseId, $userId)
    {
        $sql = 'SELECT sum(TIME) FROM `course_task_result` WHERE `courseId` = ? AND `userId`= ?';

        return $this->db()->fetchColumn($sql, array($courseId, $userId));
    }

    public function sumLearnTimeByPostIdAndUserId($postId, $userId)
    {
        $sql = "SELECT sum(time) FROM {$this->table()} JOIN `post_course` ON course_task_result.courseId = post_course.courseId WHERE `postId` = ? AND `userId` = ? ";

        return $this->db()->fetchColumn($sql, array($postId, $userId));
    }

    public function sumLearnTimeByUserId($userId)
    {
        $sql = 'SELECT sum(TIME) FROM `course_task_result` WHERE `userId`= ?';

        return $this->db()->fetchColumn($sql, array($userId));
    }

    public function sumLearnTimeByTaskIdAndUserId($taskId, $userId)
    {
        $sql = 'SELECT sum(time) FROM `course_task_result` WHERE `courseTaskId` = ? AND `userId`= ?';

        return $this->db()->fetchColumn($sql, array($taskId, $userId));
    }

    public function sumLearnTimeByCategoryIdAndUserId($categoryId, $userId)
    {
        $sql = "SELECT sum(time) FROM {$this->table()} JOIN `course_set_v8` ON course_task_result.courseId = course_set_v8.defaultCourseId WHERE `categoryId` = ? AND `userId` = ?";

        return $this->db()->fetchColumn($sql, array($categoryId, $userId));
    }

    public function sumWatchTimeByCourseIdAndUserId($courseId, $userId)
    {
        $sql = 'SELECT sum(watchTime) FROM `course_task_result` WHERE`courseId` = ? AND `userId`= ?';

        return $this->db()->fetchColumn($sql, array($courseId, $userId));
    }

    public function sumWatchTimeByPostIdAndUserId($postId, $userId)
    {
        $sql = "SELECT sum(watchTime) FROM {$this->table()} JOIN `post_course` ON course_task_result.courseId = post_course.courseId WHERE `postId` = ? AND `userId` = ? ";

        return $this->db()->fetchColumn($sql, array($postId, $userId));
    }

    public function sumWatchTimeByTaskIdAndUserId($taskId, $userId)
    {
        $sql = 'SELECT sum(watchTime) FROM `course_task_result` WHERE`courseTaskId` = ? AND `userId`= ?';

        return $this->db()->fetchColumn($sql, array($taskId, $userId));
    }

    public function sumWatchTimeByCategoryIdAndUserId($categoryId, $userId)
    {
        $sql = "SELECT sum(watchTime) FROM {$this->table()} JOIN `course_set_v8` ON course_task_result.courseId = course_set_v8.defaultCourseId WHERE `categoryId` = ?AND `userId` = ?";

        return $this->db()->fetchColumn($sql, array($categoryId, $userId));
    }

    public function sumLearnTimeByCourseIdsAndUserIdsGroupByUserId(array $courseIds, array $userIds)
    {
        $builder = $this->createQueryBuilder(array('courseIds' => $courseIds, 'userIds' => $userIds))
            ->select('sum(time) as totalLearnTime, userId')
            ->groupBy('userId');

        return $builder->execute()->fetchAll();
    }

    public function sumLearnTimeByCourseId($courseId)
    {
        $sql = "SELECT SUM(time) AS totalLearnTime FROM {$this->table} WHERE courseId = ?";

        return $this->db()->fetchColumn($sql, array($courseId));
    }

    public function sumCompulsoryTasksLearnTimeByCourseIdAndUserId($courseId, $userId)
    {
        $sql = 'SELECT SUM(ctr.time) FROM course_task_result AS ctr JOIN course_task ct ON ct.id = ctr.courseTaskId where userId = ? AND ct.courseId = ? AND ct.isOptional = 0';

        return $this->db()->fetchColumn($sql, array($userId, $courseId)) ?: 0;
    }

    public function sumCompulsoryTasksLearnTimeByCourseIdAndUserIds($courseId, $userIds)
    {
        $marks = str_repeat('?,', count($userIds) - 1).'?';
        $sql = "SELECT SUM(ctr.time) FROM course_task_result AS ctr JOIN course_task ct ON ct.id = ctr.courseTaskId where ctr.userId IN ({$marks}) AND ct.courseId = ? AND ct.isOptional = 0 GROUP BY userId";

        $parameters = array_merge($userIds, array($courseId));

        return $this->db()->fetchColumn($sql, $parameters) ?: 0;
    }

    public function countCompulsoryTaskResultsByCourseIdAndUserIds($courseId, $userIds)
    {
        $marks = str_repeat('?,', count($userIds) - 1).'?';

        $sql = "SELECT COUNT(ctr.id) FROM course_task_result AS ctr JOIN course_task ct ON ct.id = ctr.courseTaskId where ctr.userId IN ({$marks}) AND ct.courseId = ? AND ct.isOptional = 0";

        $parameters = array_merge($userIds, array($courseId));

        return $this->db()->fetchColumn($sql, $parameters);
    }

    public function declares()
    {
        $declares = parent::declares();
        array_push($declares['conditions'], 'updatedTime >= :updatedTime_GE');
        array_push($declares['conditions'], 'userId IN (:userIds)');

        return $declares;
    }
}
