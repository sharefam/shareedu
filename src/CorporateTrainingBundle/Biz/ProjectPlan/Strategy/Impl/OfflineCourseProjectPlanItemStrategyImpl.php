<?php

namespace CorporateTrainingBundle\Biz\ProjectPlan\Strategy\Impl;

use AppBundle\Common\ArrayToolkit;
use CorporateTrainingBundle\Biz\ProjectPlan\Strategy\ProjectPlanItemStrategy;
use CorporateTrainingBundle\Biz\ProjectPlan\Strategy\BaseProjectPlanItemStrategy;
use SurveyPlugin\Biz\Survey\Service\SurveyResultService;

class OfflineCourseProjectPlanItemStrategyImpl extends BaseProjectPlanItemStrategy implements ProjectPlanItemStrategy
{
    public function createItems($projectPlanId, $items, $itemType = 'offline_course')
    {
        $this->checkProjectPlanExist($projectPlanId);

        $items = $this->buildItems($items);
        $items['projectPlanId'] = $projectPlanId;
        $offlineCourse = $this->getOfflineCourseService()->createOfflineCourse($items);
        $teacherIds = ArrayToolkit::column(json_decode($items['teachers'], true), 'id');
        $this->getOfflineCourseService()->setTeachers($offlineCourse['id'], $teacherIds);
        $this->getOfflineCourseService()->publishOfflineCourse($offlineCourse['id']);

        $itemsNum = $this->getProjectPlanService()->countProjectPlanItems(
            array(
                'projectPlanId' => $projectPlanId,
            )
        );

        $projectPlanItem = array(
            'targetId' => $offlineCourse['id'],
            'targetType' => $itemType,
            'projectPlanId' => $projectPlanId,
            'seq' => $itemsNum + 1,
        );

        $this->getProjectPlanService()->createProjectPlanItem($projectPlanItem);
    }

    public function updateItem($id, $item, $itemType = null)
    {
        $fields = $this->getProjectPlanService()->getProjectPlanItem($id);
        $item = $this->buildItems($item);
        $offlineCourse = $this->getOfflineCourseService()->getOfflineCourse($fields['targetId']);

        if (!empty($item['teachers'])) {
            $teacherIds = ArrayToolkit::column(json_decode($item['teachers'], true), 'id');
            $this->getOfflineCourseService()->setTeachers($offlineCourse['id'], $teacherIds);
        }

        $fields = array(
            'title' => $item['title'],
        );
        $this->getOfflineCourseService()->updateOfflineCourse($offlineCourse['id'], $fields);
    }

    public function deleteItem($item)
    {
        $this->getOfflineCourseService()->closeOfflineCourse($item['targetId']);
    }

    public function getItem($item)
    {
        $offlineCourse = $this->getOfflineCourseService()->getOfflineCourse($item['targetId']);
        $item['detail'] = $offlineCourse;
        $offlineCourseTasks = $this->getOfflineCourseTaskService()->findTasksByOfflineCourseId($offlineCourse['id']);
        $item['tasks'] = $offlineCourseTasks;

        return $item;
    }

    public function getTaskReviewNum($taskId)
    {
        $reviewNum = $this->getOfflineCourseTaskService()->countTaskResults(array('homeworkStatus' => 'submitted', 'taskId' => $taskId));

        return $reviewNum;
    }

    public function getStudyResult($item, $user)
    {
        return array();
    }

    public function getItemInfoByUserId($item, $userId)
    {
        $offlineCourseTasks = $this->getOfflineCourseService()->findOfflineCourseItems($item['targetId']);
        $offlineCourseTaskResults = $this->getOfflineCourseTaskService()->findTaskResultsByUserId($userId);
        $offlineCourseTaskResults = ArrayToolkit::index($offlineCourseTaskResults, 'taskId');

        foreach ($offlineCourseTasks as $offlineCourseTask) {
            $offlineCourseTaskInfo[] = array(
                'taskId' => $offlineCourseTask['id'],
                'mediaId' => $offlineCourseTask['activity']['mediaId'],
                'mediaType' => $offlineCourseTask['activity']['mediaType'],
                'title' => $offlineCourseTask['title'],
                'place' => $offlineCourseTask['place'],
                'startTime' => $offlineCourseTask['startTime'],
                'endTime' => $offlineCourseTask['endTime'],
                'hasHomework' => $offlineCourseTask['hasHomework'],
                'homeworkDemand' => $offlineCourseTask['homeworkDemand'],
                'homeworkDeadline' => $offlineCourseTask['homeworkDeadline'],
                'attendStatus' => $this->getAttendStatus($offlineCourseTask['id'], $userId),
                'homeworkStatus' => isset($offlineCourseTaskResults[$offlineCourseTask['id']]) ? $offlineCourseTaskResults[$offlineCourseTask['id']]['homeworkStatus'] : 'unsubmit',
                'questionnaireStatus' => ($offlineCourseTask['activity']['mediaType'] == 'offlineCourseQuestionnaire') ? $this->getQuestionnaireStatus($offlineCourseTask['activity']['mediaId'], $userId) : 'notStart',
            );
        }

        return isset($offlineCourseTaskInfo) ? $offlineCourseTaskInfo : array();
    }

    public function findItemsDetail($items)
    {
        $offlineCourseIds = ArrayToolkit::column($items, 'targetId');

        $offlineCourses = $this->getOfflineCourseService()->findOfflineCoursesByIds($offlineCourseIds);
        $offlineCourses = ArrayToolkit::index($offlineCourses, 'id');

        foreach ($items as &$item) {
            if (isset($offlineCourses[$item['targetId']])) {
                $item['detail'] = $offlineCourses[$item['targetId']];
            }
        }

        return ArrayToolkit::index($items, 'id');
    }

    public function findTasksByItemIdAndTimeRange($offlineCourseId, $timeRange)
    {
        $tasks = $this->getOfflineCourseTaskService()->findTasksByOfflineCourseIdAndTypeAndTimeRange($offlineCourseId, 'offlineCourse', $timeRange);
        if (!empty($tasks)) {
            $offlineCourseTasks = ArrayToolkit::index($tasks, 'id');
            $offlineCourseQuestionnaire = $this->getOfflineCourseTaskService()->searchTasks(array('offlineCourseId' => $offlineCourseId, 'type' => 'questionnaire'), array(), 0, PHP_INT_MAX);
            $offlineCourseQuestionnaire = ArrayToolkit::index($offlineCourseQuestionnaire, 'id');
            $tasks = array_merge($offlineCourseQuestionnaire, $offlineCourseTasks);
        }

        return $tasks;
    }

    public function isFinished($item, $user)
    {
        $result = false;
        $offlineCourse = $this->getOfflineCourseService()->getOfflineCourse($item['targetId']);
        $offlineCourseMembers = $this->getOfflineCourseMemberService()->searchMembers(
            array('offlineCourseId' => $offlineCourse['id'], 'userId' => $user['id'], 'learnedNumGreaterThan' => $offlineCourse['taskNum']),
            array('id' => 'ASC'),
            0,
            PHP_INT_MAX
        );

        if (empty($offlineCourseMembers)) {
            return $result;
        }

        $memberNum = $this->getProjectPlanMemberService()->countProjectPlanMembers(array('userIds' => ArrayToolkit::column($offlineCourseMembers, 'userId'), 'projectPlanId' => $item['projectPlanId']));

        if ($memberNum == 1) {
            return true;
        }

        return $result;
    }

    public function findFinishedMembers($item)
    {
        $offlineCourse = $this->getOfflineCourseService()->getOfflineCourse($item['targetId']);
        $offlineCourseMembers = $this->getOfflineCourseMemberService()->searchMembers(
            array('offlineCourseId' => $offlineCourse['id'], 'learnedNumGreaterThan' => $offlineCourse['taskNum']),
            array('id' => 'ASC'),
            0,
            PHP_INT_MAX
        );

        if (empty($offlineCourseMembers)) {
            return array();
        }

        return $this->getProjectPlanMemberService()->searchProjectPlanMembers(
            array('userIds' => ArrayToolkit::column($offlineCourseMembers, 'userId'), 'projectPlanId' => $item['projectPlanId']),
            array('id' => 'ASC'),
            0,
            PHP_INT_MAX
        );
    }

    public function getFinishedMembersNum($item)
    {
        $offlineCourse = $this->getOfflineCourseService()->getOfflineCourse($item['targetId']);
        $offlineCourseMembers = $this->getOfflineCourseMemberService()->searchMembers(
            array('offlineCourseId' => $offlineCourse['id'], 'learnedNumGreaterThan' => $offlineCourse['taskNum']),
            array('id' => 'ASC'),
            0,
            PHP_INT_MAX
        );

        if (empty($offlineCourseMembers)) {
            return 0;
        }

        return $this->getProjectPlanMemberService()->countProjectPlanMembers(array('userIds' => ArrayToolkit::column($offlineCourseMembers, 'userId'), 'projectPlanId' => $item['projectPlanId']));
    }

    protected function getQuestionnaireStatus($surveyId, $userId)
    {
        $result = $this->getSurveyResultService()->getSurveyResultByUserIdAndSurveyIdAndStatus($userId, $surveyId, 'finished');

        if ($result) {
            return $result['status'];
        }

        return 'notStart';
    }

    protected function buildItems($params)
    {
        return $params;
    }

    protected function getAttendStatus($offlineCourseTaskId, $userId)
    {
        $offlineCourseTaskResult = $this->getOfflineCourseTaskService()->getTaskResultByTaskIdAndUserId($offlineCourseTaskId, $userId);
        $offlineCourseTask = $this->getOfflineCourseTaskService()->getTask($offlineCourseTaskId);

        if ($offlineCourseTask['endTime'] < time() && !empty($offlineCourseTask['endTime'])) {
            return 'absent';
        }

        return !empty($offlineCourseTaskResult['attendStatus']) ? $offlineCourseTaskResult['attendStatus'] : 'unattended';
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineCourse\Service\Impl\TaskServiceImpl
     */
    protected function getOfflineCourseTaskService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineCourse:TaskService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineCourse\Service\Impl\OfflineCourseServiceImpl
     */
    protected function getOfflineCourseService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineCourse:OfflineCourseService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineCourse\Service\Impl\MemberServiceImpl
     */
    protected function getOfflineCourseMemberService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineCourse:MemberService');
    }

    protected function getOfflineCourseAttendanceService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineCourse:MemberService');
    }

    protected function getOfflineCourseActivityService()
    {
        return $this->createService('CorporateTrainingBundle:Activity:OfflineCourseActivityService');
    }

    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    /**
     * @return SurveyResultService
     */
    protected function getSurveyResultService()
    {
        return $this->createService('SurveyPlugin:Survey:SurveyResultService');
    }
}
