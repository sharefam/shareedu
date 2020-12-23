<?php

namespace CorporateTrainingBundle\Biz\Course\Dao\Impl;

use Biz\Course\Dao\Impl\CourseMemberDaoImpl as BaseCourseMemberDaoImpl;
use CorporateTrainingBundle\Biz\Course\Dao\CourseMemberDao;

class CourseMemberDaoImpl extends BaseCourseMemberDaoImpl implements CourseMemberDao
{
    public function findMembersByCourseIdsAndRole($courseIds, $role)
    {
        if (empty($courseIds)) {
            return array();
        }
        $marks = str_repeat('?,', count($courseIds) - 1).'?';

        $sql = "SELECT * FROM {$this->table} WHERE role = ? AND  courseId IN ({$marks}) ORDER BY seq, createdTime DESC";

        return $this->db()->fetchAll($sql, array_merge(array($role), $courseIds));
    }

    public function getFinishedTimeByCourseIdAndUserId($courseId, $userId)
    {
        $sql = "SELECT finishedTime FROM {$this->table()} m ";
        $sql .= ' INNER JOIN course_v8 c ON m.courseId = c.id ';
        $sql .= 'WHERE m.courseId = ? AND m.userId = ? AND ';
        $sql .= 'm.learnedCompulsoryTaskNum >= c.compulsoryTaskNum ';

        return $this->db()->fetchColumn($sql, array($courseId, $userId));
    }

    public function declares()
    {
        $declares = parent::declares();
        array_push($declares['conditions'], 'learnedNum = :learnedNum');

        return $declares;
    }

    protected function applySqlParams($conditions, $sql)
    {
        $params = array();
        $conditions = array_filter($conditions, function ($value) {
            return !empty($value);
        });
        foreach ($conditions as $key => $value) {
            if (!is_array($value)) {
                $sql .= $key.' = ? AND ';
                array_push($params, $value);
            } else {
                $arguments = implode(',', $value);
                $sql .= $key.' IN ('.$arguments.') AND ';
            }
        }

        return array($sql, $params);
    }
}
