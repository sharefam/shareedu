<?php

namespace CorporateTrainingBundle\Biz\OfflineCourse\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use CorporateTrainingBundle\Biz\OfflineCourse\Dao\OfflineCourseDao;

class OfflineCourseDaoImpl extends GeneralDaoImpl implements OfflineCourseDao
{
    protected $table = 'offline_course';

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function findPublishedOfflineCoursesByTeacherIdAndTimeRange($teacherId, $timeRange)
    {
        $sql = "SELECT c.* FROM `offline_course` c LEFT JOIN `project_plan` p ON c.projectPlanId = p.id WHERE c.teacherIds = ? AND p.status = 'published' AND ((c.startTime >= ? AND c.startTime <= ?) OR (c.endTime >= ? AND c.endTime <= ?) OR (c.startTime <= ? AND c.endTime >= ?))";

        $parameters = array_merge(array('|'.$teacherId.'|'), array_values($timeRange), array_values($timeRange), array_values($timeRange));

        return $this->db()->fetchAll($sql, $parameters);
    }

    public function findPublishedCourseByUserIds($userIds)
    {
        $marks = str_repeat('?,', count($userIds) - 1).'?';

        $sql = "SELECT c.* FROM offline_course_member m LEFT JOIN offline_course c on m.offlineCourseId = c.id WHERE c.status = ? AND m.userId IN ({$marks})";

        return $this->db()->fetchAll($sql, array_merge(array('published'), $userIds) ?: array());
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'serializes' => array(
                'teacherIds' => 'delimiter',
                'cover' => 'json',
            ),
            'orderbys' => array('id', 'createdTime', 'updatedTime', 'endTime'),
            'conditions' => array(
                'id = :id',
                'userId = :userId',
                'status = :status',
                'teacherIds LIKE :teacherId',
                'projectPlanId = :projectPlanId',
                'createdTime >= :startTime',
                'createdTime <= :endTime',
                'id IN ( :ids )',
                'projectPlanId IN ( :projectPlanIds)',
                'status IN ( :statuses)',
                'status NOT IN ( :excludeStatuses)',
                'title LIKE :likeTitle',
            ),
        );
    }
}
