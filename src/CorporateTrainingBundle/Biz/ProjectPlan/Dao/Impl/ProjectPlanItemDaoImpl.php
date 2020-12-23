<?php

namespace CorporateTrainingBundle\Biz\ProjectPlan\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use CorporateTrainingBundle\Biz\ProjectPlan\Dao\ProjectPlanItemDao;

class ProjectPlanItemDaoImpl extends GeneralDaoImpl implements ProjectPlanItemDao
{
    protected $table = 'project_plan_item';

    public function getByTargetIdAndTargetType($targetId, $targetType)
    {
        return $this->getByFields(array('targetId' => $targetId, 'targetType' => $targetType));
    }

    public function getByProjectPlanIdAndTargetIdAndTargetType($projectPlanId, $targetId, $targetType)
    {
        return $this->getByFields(array('projectPlanId' => $projectPlanId, 'targetId' => $targetId, 'targetType' => $targetType));
    }

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function findByProjectPlanId($projectPlanId)
    {
        return $this->findByFields(array('projectPlanId' => $projectPlanId));
    }

    public function findByProjectPlanIds($projectPlanIds)
    {
        return $this->findInField('projectPlanId', $projectPlanIds);
    }

    public function findByProjectPlanIdAndTargetType($projectPlanId, $targetType)
    {
        return $this->findByFields(array('projectPlanId' => $projectPlanId, 'targetType' => $targetType));
    }

    public function findByTargetIdAndTargetType($targetId, $targetType)
    {
        return $this->findByFields(array('targetId' => $targetId, 'targetType' => $targetType));
    }

    public function findHasFinishedSurveyResultProjectPlanItemIds($projectPlanId, $userIds)
    {
        $userIdMarks = str_repeat('?,', count($userIds) - 1).'?';

        $sql = "SELECT distinct(i.id) FROM {$this->table} i INNER JOIN `activity` a INNER JOIN `plugin_survey_result` r ON a.mediaId = r.surveyId AND i.targetId = a.fromCourseId AND ((a.mediaType = 'questionnaire' AND i.targetType = 'course') OR (a.mediaType = 'offlineCourseQuestionnaire' AND i.targetType = 'offline_course')) WHERE i.projectPlanId = ? AND r.userId IN ({$userIdMarks}) AND r.status = 'finished'";

        $parameters = array_merge(array($projectPlanId), $userIds);

        return $this->db()->fetchAll($sql, $parameters);
    }

    public function deleteItemByProjectPlanId($projectPlanId)
    {
        $sql = "DELETE FROM {$this->table} WHERE projectPlanId = ?";

        return $this->db()->executeUpdate($sql, array($projectPlanId));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime', 'startTime'),
            'orderbys' => array(
                'id',
                'createdTime',
                'updatedTime',
                'startTime',
                'endTime',
                'seq',
            ),
            'conditions' => array(
                'id = :id',
                'id IN (:ids)',
                'projectPlanId = :projectPlanId',
                'targetType = :targetType',
                'targetType IN (:targetTypes)',
                'targetId = :targetId',
                'targetId IN (:targetIds)',
                'projectPlanId IN (:projectPlanIds)',
                'startTime >= :startTime_GE',
                'targetType NOT IN ( :excludeTargetTypes )',
            ),
        );
    }
}
