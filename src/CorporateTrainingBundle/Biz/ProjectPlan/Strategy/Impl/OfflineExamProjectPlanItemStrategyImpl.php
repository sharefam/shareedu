<?php

namespace CorporateTrainingBundle\Biz\ProjectPlan\Strategy\Impl;

use AppBundle\Common\ArrayToolkit;
use CorporateTrainingBundle\Biz\ProjectPlan\Strategy\ProjectPlanItemStrategy;
use CorporateTrainingBundle\Biz\ProjectPlan\Strategy\BaseProjectPlanItemStrategy;

class OfflineExamProjectPlanItemStrategyImpl extends BaseProjectPlanItemStrategy implements ProjectPlanItemStrategy
{
    public function createItems($projectPlanId, $items, $itemType = 'offline_exam')
    {
        $this->checkProjectPlanExist($projectPlanId);

        $items = $this->buildItems($items);
        $items['projectPlanId'] = $projectPlanId;
        $offlineExam = $this->getOfflineExamService()->createOfflineExam($items);
        $this->getOfflineExamService()->publishOfflineExam($offlineExam['id']);
        $itemsNum = $this->getProjectPlanService()->countProjectPlanItems(
            array(
                'projectPlanId' => $projectPlanId,
            )
        );

        $projectPlanItem = array(
            'targetId' => $offlineExam['id'],
            'targetType' => $itemType,
            'projectPlanId' => $projectPlanId,
            'seq' => $itemsNum + 1,
            'startTime' => $items['startTime'],
            'endTime' => $items['endTime'],
        );

        $this->getProjectPlanService()->createProjectPlanItem($projectPlanItem);

        $this->dispatchEvent('project_plan.create.offline_exam', $offlineExam);
    }

    public function updateItem($id, $item, $itemType = null)
    {
        $fields = $this->getProjectPlanService()->getProjectPlanItem($id);
        $item = $this->buildItems($item);
        $exam = $this->getOfflineExamService()->updateOfflineExam($fields['targetId'], $item);
        $this->getProjectPlanService()->updateProjectPlanItem($id, array('startTime' => $item['startTime'], 'endTime' => $item['endTime']));

        $this->dispatchEvent('project_plan.update.offline_exam', $exam);
    }

    public function deleteItem($item)
    {
        $exam = $this->getOfflineExamService()->closeOfflineExam($item['targetId']);

        $this->dispatchEvent('project_plan.delete.offline_exam', $exam);
    }

    public function getItem($item)
    {
        $offlineExam = $this->getOfflineExamService()->getOfflineExam($item['targetId']);
        $item['detail'] = $offlineExam;

        return $item;
    }

    public function getTaskReviewNum($taskId)
    {
        return null;
    }

    public function getItemInfoByUserId($item, $user)
    {
        return array();
    }

    public function getStudyResult($item, $userId)
    {
        return array();
    }

    public function findItemsDetail($items)
    {
        $offlineExamIds = ArrayToolkit::column($items, 'targetId');

        $offlineExams = $this->getOfflineExamService()->findOfflineExamByIds($offlineExamIds);
        $offlineExams = ArrayToolkit::index($offlineExams, 'id');

        foreach ($items as &$item) {
            if (isset($offlineExams[$item['targetId']])) {
                $item['detail'] = $offlineExams[$item['targetId']];
            }
        }

        return ArrayToolkit::index($items, 'id');
    }

    public function findTasksByItemIdAndTimeRange($offlineExamId, $timeRange)
    {
        $tasks = $this->getOfflineExamService()->getOfflineExamByIdAndTimeRange($offlineExamId, $timeRange);

        return $tasks;
    }

    public function isFinished($item, $user)
    {
        $offlineMember = $this->getOfflineExamMemberService()->getMemberByOfflineExamIdAndUserId($item['targetId'], $user['id']);

        $result = true;

        if (empty($offlineMember)) {
            $result = false;
        }

        return $result;
    }

    public function findFinishedMembers($item)
    {
        $offlineExam = $this->getOfflineExamService()->getOfflineExam($item['targetId']);
        $offlineExamMembers = $this->getOfflineExamMemberService()->searchMembers(
            array('offlineExamId' => $offlineExam['id']),
            array('id' => 'ASC'),
            0,
            PHP_INT_MAX
        );

        if (empty($offlineExamMembers)) {
            return array();
        }

        return $this->getProjectPlanMemberService()->searchProjectPlanMembers(
            array('userIds' => ArrayToolkit::column($offlineExamMembers, 'userId'), 'projectPlanId' => $item['projectPlanId']),
            array('id' => 'ASC'),
            0,
            PHP_INT_MAX
        );
    }

    public function getFinishedMembersNum($item)
    {
        $offlineExam = $this->getOfflineExamService()->getOfflineExam($item['targetId']);
        $offlineExamMembers = $this->getOfflineExamMemberService()->searchMembers(
            array('offlineExamId' => $offlineExam['id']),
            array('id' => 'ASC'),
            0,
            PHP_INT_MAX
        );

        if (empty($offlineExamMembers)) {
            return 0;
        }

        return $this->getProjectPlanMemberService()->countProjectPlanMembers(array('userIds' => ArrayToolkit::column($offlineExamMembers, 'userId'), 'projectPlanId' => $item['projectPlanId']));
    }

    protected function buildItems($params)
    {
        if (!empty($params['startTime'])) {
            $params['startTime'] = strtotime($params['startTime']);
        }

        if (!empty($params['endTime'])) {
            $params['endTime'] = strtotime($params['endTime']);
        }

        return $params;
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineExam\Service\Impl\OfflineExamServiceImpl
     */
    protected function getOfflineExamService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineExam:OfflineExamService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineExam\Service\Impl\MemberServiceImpl
     */
    protected function getOfflineExamMemberService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineExam:MemberService');
    }
}
