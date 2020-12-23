<?php

namespace CorporateTrainingBundle\Biz\ProjectPlan\Strategy\Impl;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Exception\AccessDeniedException;
use CorporateTrainingBundle\Biz\ProjectPlan\Strategy\ProjectPlanItemStrategy;
use CorporateTrainingBundle\Biz\ProjectPlan\Strategy\BaseProjectPlanItemStrategy;
use ExamPlugin\Biz\TestPaper\Service\TestPaperService;

class ExamProjectPlanItemStrategyImpl extends BaseProjectPlanItemStrategy implements ProjectPlanItemStrategy
{
    public function createItems($projectPlanId, $items, $itemType = 'exam')
    {
        $this->checkProjectPlanExist($projectPlanId);

        $items = $this->buildItems($items);

        $currentUser = $this->biz['user'];
        $testPaper = $this->getTestPaperService()->getTestPaper($items['mediaId']);
        $hasPermission = $this->getManagePermissionService()->checkResourceUsePermission('pluginTestPaper', $items['mediaId'], $testPaper['orgId']);
        if (!$hasPermission) {
            throw new AccessDeniedException('TestPaper Not Use Permission');
        }

        $exam = array(
            'name' => $items['name'],
            'type' => 'grade',
            'testPaperId' => $items['mediaId'],
            'status' => 'published',
            'startTime' => $items['startTime'],
            'endTime' => $items['endTime'],
            'length' => $items['length'] * 60,
            'passScore' => $items['passScore'],
            'resitTimes' => $items['resitTimes'],
            'showAnswerAndAnalysis' => $items['showAnswerAndAnalysis'],
            'orgId' => $currentUser->getCurrentOrgId(),
            'projectPlanId' => $projectPlanId,
        );

        if (empty($items['examId'])) {
            $exam = $this->getExamService()->createExam($exam);
            $this->getExamService()->publishExam($exam['id']);

            $itemsNum = $this->getProjectPlanService()->countProjectPlanItems(
                array(
                    'projectPlanId' => $projectPlanId,
                )
            );

            $projectPlanItem = array(
                'targetId' => $exam['id'],
                'targetType' => $itemType,
                'projectPlanId' => $projectPlanId,
                'seq' => $itemsNum + 1,
                'startTime' => $items['startTime'],
                'endTime' => $items['endTime'],
            );

            $this->getProjectPlanService()->createProjectPlanItem($projectPlanItem);

            $this->dispatchEvent('project_plan.create.exam', $exam);
        }
    }

    public function updateItem($id, $item, $itemType = null)
    {
        $fields = $this->getProjectPlanService()->getProjectPlanItem($id);
        $item = $this->buildItems($item);
        $currentUser = $this->biz['user'];
        $exam = array(
            'name' => $item['name'],
            'type' => 'grade',
            'testPaperId' => $item['mediaId'],
            'status' => 'published',
            'startTime' => $item['startTime'],
            'endTime' => $item['endTime'],
            'length' => $item['length'] * 60,
            'passScore' => $item['passScore'],
            'resitTimes' => $item['resitTimes'],
            'showAnswerAndAnalysis' => $item['showAnswerAndAnalysis'],
            'orgId' => $currentUser->getCurrentOrgId(),
            'projectPlanId' => $item['projectPlanId'],
        );
        $exam = $this->getExamService()->updateExamBaseInfo($fields['targetId'], $exam);
        $this->getProjectPlanService()->updateProjectPlanItem($id, array('startTime' => $item['startTime'], 'endTime' => $item['endTime']));
        $this->dispatchEvent('project_plan.update.exam', $exam);
    }

    public function getItem($item)
    {
        $exam = $this->getExamService()->getExam($item['targetId']);
        $item['detail'] = $exam;

        return $item;
    }

    public function getTaskReviewNum($examId)
    {
        $reviewNum = $this->getExamService()->countResults(array('examId' => $examId, 'status' => 'reviewing'));

        return $reviewNum;
    }

    public function getStudyResult($item, $user)
    {
        $result = $this->getExamService()->getUserBestExamResult($user['id'], $item['targetId']);

        if (empty($result)) {
            return array();
        }

        return $result;
    }

    public function getItemInfoByUserId($item, $userId)
    {
        $exam = $this->getExamService()->getExam($item['targetId']);
        $testPaper = $this->getTestPaperService()->getTestPaper($exam['testPaperId']);

        $taskInfo = array();

        $taskInfo['examTimeStatus'] = $this->getExamTimeStatus($exam['startTime'], $exam['endTime']);
        $taskInfo['testPaperScore'] = $testPaper['score'];
        $taskInfo['resitTimes'] = $item['detail']['resitTimes'];
        $taskInfo['remainingResitTimes'] = $this->getExamResitTimes($item, $userId);

        return $taskInfo;
    }

    public function findItemsDetail($items)
    {
        $examIds = ArrayToolkit::column($items, 'targetId');
        $exams = $this->getExamService()->findExamsByIds($examIds);
        $exams = ArrayToolkit::index($exams, 'id');

        foreach ($items as &$item) {
            if (isset($exams[$item['targetId']])) {
                $item['detail'] = $exams[$item['targetId']];
            }
        }

        return ArrayToolkit::index($items, 'id');
    }

    public function findTasksByItemIdAndTimeRange($examId, $timeRange)
    {
        $tasks = $this->getExamService()->getExamByIdAndTimeRange($examId, $timeRange);

        return $tasks;
    }

    public function isFinished($item, $user)
    {
        $exam = $this->getExamService()->getExam($item['targetId']);
        $examResults = $this->getExamService()->findExamResultsByExamIdAndUserId($exam['id'], $user['id']);

        foreach ($examResults as $examResult) {
            if ('finished' == $examResult['status']) {
                return true;
            }
        }

        return false;
    }

    public function findFinishedMembers($item)
    {
        $exam = $this->getExamService()->getExam($item['targetId']);
        $examMembers = $this->getExamService()->searchMembers(
            array('examId' => $exam['id'], 'status' => 'finished'),
            array('id' => 'ASC'),
            0,
            PHP_INT_MAX
        );

        if (empty($examMembers)) {
            return array();
        }

        return $this->getProjectPlanMemberService()->searchProjectPlanMembers(
            array('userIds' => ArrayToolkit::column($examMembers, 'userId'), 'projectPlanId' => $item['projectPlanId']),
            array('id' => 'ASC'),
            0,
            PHP_INT_MAX
        );
    }

    public function getFinishedMembersNum($item)
    {
        $exam = $this->getExamService()->getExam($item['targetId']);
        $examMembers = $this->getExamService()->searchMembers(
            array('examId' => $exam['id'], 'status' => 'finished'),
            array('id' => 'ASC'),
            0,
            PHP_INT_MAX
        );

        if (empty($examMembers)) {
            return 0;
        }

        return $this->getProjectPlanMemberService()->countProjectPlanMembers(array('userIds' => ArrayToolkit::column($examMembers, 'userId'), 'projectPlanId' => $item['projectPlanId']));
    }

    protected function buildItems($params)
    {
        if (!empty($params['startTime'])) {
            $params['startTime'] = strtotime($params['startTime']);
        }
        if (!empty($params['endTime'])) {
            $params['endTime'] = strtotime($params['endTime']);
        }

        if (!empty($params['showAnswerAndAnalysis']) && 'on' == $params['showAnswerAndAnalysis']) {
            $params['showAnswerAndAnalysis'] = 1;
        } else {
            $params['showAnswerAndAnalysis'] = 0;
        }

        return $params;
    }

    public function deleteItem($item)
    {
        $this->getExamService()->closeExam($item['targetId']);
    }

    protected function parseTimeFields($fields)
    {
        if (!empty($fields['length'])) {
            $fields['length'] = $fields['length'] * 60;
        }

        return $fields;
    }

    protected function getExamTimeStatus($startTime, $endTime)
    {
        $currentTime = time();
        if ($currentTime < $startTime) {
            return 'notStart';
        }

        if ($startTime <= $currentTime && $currentTime <= $endTime) {
            return 'ongoing';
        }

        return 'expired';
    }

    protected function getExamResitTimes($projectPlanItem, $userId)
    {
        $exam = $this->getExamService()->getExam($projectPlanItem['targetId']);
        $member = $this->getExamService()->getMemberByExamIdIdAndUserId($projectPlanItem['targetId'], $userId);
        if (empty($exam['resitTimes'])) {
            return 0;
        }

        return $exam['resitTimes'] - $member['finishedCount'];
    }

    /**
     * @return \ExamPlugin\Biz\Exam\Service\Impl\ExamServiceImpl
     */
    protected function getExamService()
    {
        return $this->createService('ExamPlugin:Exam:ExamService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\Impl\ProjectPlanServiceImpl
     */
    protected function getProjectPlanService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:ProjectPlanService');
    }

    /**
     * @return TestPaperService
     */
    protected function getTestPaperService()
    {
        return $this->createService('ExamPlugin:TestPaper:TestPaperService');
    }
}
