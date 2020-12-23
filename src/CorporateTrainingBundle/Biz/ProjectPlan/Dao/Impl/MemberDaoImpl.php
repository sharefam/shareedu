<?php

namespace CorporateTrainingBundle\Biz\ProjectPlan\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use CorporateTrainingBundle\Biz\ProjectPlan\Dao\MemberDao;

class MemberDaoImpl extends GeneralDaoImpl implements MemberDao
{
    protected $table = 'project_plan_member';

    public function getByUserIdAndProjectPlanId($userId, $projectPlanId)
    {
        return $this->getByFields(array('userId' => $userId, 'projectPlanId' => $projectPlanId));
    }

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function findByUserId($userId)
    {
        return $this->findByFields(array('userId' => $userId));
    }

    public function findByProjectPlanId($projectPlanId)
    {
        return $this->findByFields(array('projectPlanId' => $projectPlanId));
    }

    public function deleteMemberByProjectPlanId($projectPlanId)
    {
        $sql = "DELETE FROM {$this->table} WHERE projectPlanId = ?";

        return $this->db()->executeUpdate($sql, array($projectPlanId));
    }

    public function calculateProjectPlanLearnDataByUserIdsAndDate($userIds, $date)
    {
        $userMarks = str_repeat('?,', count($userIds) - 1).'?';

        $sql = "SELECT m.userId, COUNT(DISTINCT m.projectPlanId) as learnedProjectPlanNum, GROUP_CONCAT(m.projectPlanId SEPARATOR '|') AS learnedProjectPlanIds  FROM `project_plan_member` m LEFT JOIN `project_plan` p ON m.projectPlanId = p.id AND m.userId IN ({$userMarks}) WHERE (p.startTime >= ? AND p.startTime <= ?) OR (p.endTime >= ? AND p.endTime <= ?) OR (p.startTime <= ? AND p.endTime >= ?) GROUP BY m.userId";

        $parameters = array_merge($userIds, array_values($date), array_values($date), array_values($date));

        return $this->db()->fetchAll($sql, $parameters);
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'orderbys' => array('id', 'projectPlanId', 'createdTime', 'updatedTime'),
            'conditions' => array(
                'id = :id',
                'projectPlanId = :projectPlanId',
                'userId = :userId',
                'id IN ( :ids)',
                'finishedTime = :finishedTime',
                'userId IN ( :userIds)',
                'userId NOT IN ( :excludeUserIds )',
                'projectPlanId IN ( :projectPlanIds)',
                'status = :status',
                'status IN ( :statuses)',
                'finishedTime > :finishedTime_GT',
                'createdTime <= :createdTime_LE',
                'createdTime >= :createdTime_GE',
            ),
        );
    }
}
