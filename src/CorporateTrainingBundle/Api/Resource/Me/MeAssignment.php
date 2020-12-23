<?php

namespace CorporateTrainingBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Util\AssetHelper;
use AppBundle\Common\ArrayToolkit;
use CorporateTrainingBundle\Biz\OfflineActivity\Service\MemberService;
use CorporateTrainingBundle\Biz\OfflineActivity\Service\OfflineActivityService;
use CorporateTrainingBundle\Biz\Taxonomy\Service\CategoryService;
use ExamPlugin\Biz\Exam\Service\ExamService;
use ExamPlugin\Biz\TestPaper\Service\TestPaperService;
use SurveyPlugin\Biz\Questionnaire\Service\QuestionnaireService;
use SurveyPlugin\Biz\Survey\Service\SurveyMemberService;
use SurveyPlugin\Biz\Survey\Service\SurveyService;

class MeAssignment extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $currentUser = $this->getCurrentUser();
        $activities = $this->buildUserOfflineActivities($currentUser['id']);
        $surveys = $this->buildUserSurveys($currentUser['id']);
        $exams = $this->buildUserExams($currentUser['id']);
        $projectPlans = $this->buildUserProjectPlans($currentUser['id']);
        $assignments = array_merge($activities, $surveys, $exams, $projectPlans);

        $joinTime = array();
        foreach ($assignments as $key => $assignment) {
            $joinTime[$key] = $assignment['joinTime'];
        }
        array_multisort($joinTime, SORT_DESC, $assignments);

        return $assignments;
    }

    protected function buildUserProjectPlans($userId)
    {
        $members = $this->findUserProjectPlans($userId);
        $projectPlanIds = ArrayToolkit::column($members, 'projectPlanId');
        $members = ArrayToolkit::index($members, 'projectPlanId');
        if (empty($projectPlanIds)) {
            return array();
        }

        $effectiveProjectPlans = $this->getProjectPlanService()->findUnfinishedProjectPlansByCurrentUserId(0, PHP_INT_MAX);
        $categories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($effectiveProjectPlans, 'categoryId'));
        $categories = ArrayToolkit::index($categories, 'id');
        $projectPlans = array();
        foreach ($effectiveProjectPlans as $key => $effectiveProjectPlan) {
            $projectPlans[] = array(
                'type' => 'projectPlan',
                'id' => $effectiveProjectPlan['id'],
                'title' => $effectiveProjectPlan['name'],
                'cover' => $this->transformCover($effectiveProjectPlan['cover'], 'project-plan.png'),
                'startDate' => $effectiveProjectPlan['startTime'],
                'endDate' => $effectiveProjectPlan['endTime'],
                'progress' => round($this->getProjectPlanService()->getPersonalProjectPlanProgress($effectiveProjectPlan['id'], $userId), 0),
                'categoryName' => empty($categories[$effectiveProjectPlan['categoryId']]) ? '' : $categories[$effectiveProjectPlan['categoryId']]['name'],
                'joinTime' => empty($members[$effectiveProjectPlan['id']]['createdTime']) ? '' : $members[$effectiveProjectPlan['id']]['createdTime'],
                'todayFocus' => $this->getProjectPlanService()->findTaskDetailByTimeRangeAndProjectPlanId(strtotime(date('Y-m-d', time())), strtotime(date('Y-m-d 23:59:59', time())), $effectiveProjectPlan['id']),
            );
        }

        return $projectPlans;
    }

    protected function buildUserOfflineActivities($userId)
    {
        $members = $this->findUserOfflineActivities($userId);
        $activityIds = ArrayToolkit::column($members, 'offlineActivityId');
        $members = ArrayToolkit::index($members, 'offlineActivityId');
        if (empty($activityIds)) {
            return array();
        }

        $effectiveActivities = $this->findUserEffectiveActivities($activityIds);
        $categories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($effectiveActivities, 'categoryId'));
        $categories = ArrayToolkit::index($categories, 'id');
        $activities = array();
        foreach ($effectiveActivities as $key => $effectiveActivity) {
            $activities[] = array(
                'type' => 'offlineActivity',
                'id' => $effectiveActivity['id'],
                'title' => $effectiveActivity['title'],
                'cover' => $this->transformCover($effectiveActivity['cover'], 'course.png'),
                'startDate' => $effectiveActivity['startTime'],
//              TODO  兼容app页面字段，后期app修改后删除
                'endDate' => $effectiveActivity['endTime'],
                'address' => $effectiveActivity['address'],
                'categoryName' => empty($categories[$effectiveActivity['categoryId']]) ? '' : $categories[$effectiveActivity['categoryId']]['name'],
                'joinTime' => empty($members[$effectiveActivity['id']]['createdTime']) ? '' : $members[$effectiveActivity['id']]['createdTime'],
            );
        }

        return $activities;
    }

    protected function buildUserExams($userId)
    {
        if (!$this->isPluginInstalled('Exam')) {
            return array();
        }

        $members = $this->findUserExams($userId);
        $members = ArrayToolkit::index($members, 'examId');
        if (empty($members)) {
            return array();
        }

        $examIds = ArrayToolkit::column($members, 'examId');
        $effectiveExams = $this->findUserEffectiveExams($examIds);
        $examResults = $this->getExamService()->searchExamResults(
            array('userId' => $userId, 'examIds' => $examIds),
            array('score' => 'ASC', 'createdTime' => 'ASC'),
            0,
            PHP_INT_MAX
        );
        $examResults = ArrayToolkit::index($examResults, 'examId');
        $testPaperIds = ArrayToolkit::column($effectiveExams, 'testPaperId');
        $testPapers = $this->getTestPaperService()->findTestPapersByIds($testPaperIds);
        $testPapers = ArrayToolkit::index($testPapers, 'id');
        $exams = array();
        foreach ($effectiveExams as $key => $exam) {
            $examResult = empty($examResults[$exam['id']]) ? array() : $examResults[$exam['id']];
            $testPaper = empty($testPapers[$exam['testPaperId']]) ? array() : $testPapers[$exam['testPaperId']];
            $member = $members[$exam['id']];
            $exams[] = array(
                'type' => 'exam',
                'examType' => $exam['type'],
                'id' => $exam['id'],
                'title' => $exam['name'],
                'startTime' => $exam['startTime'],
                'endTime' => $exam['endTime'],
                'examScore' => empty($testPaper['score']) ? '0.0' : $testPaper['score'],
                'userScore' => empty($examResult['score']) ? '0.0' : $examResult['score'],
                'joinTime' => $member['createdTime'],
                'memberStatus' => $member['status'],
                'passStatus' => empty($examResult['passStatus']) ? '' : $examResult['passStatus'],
                'examStatus' => $this->getAssignmentTimeStatus($exam['startTime'], $exam['endTime']),
                'finishedCount' => $member['finishedCount'],
                'resitTimes' => $exam['resitTimes'],
                'remainingResitTimes' => $this->getRemainingResitTimes($member, $exam),
            );
        }

        return $exams;
    }

    protected function buildUserSurveys($userId)
    {
        if (!$this->isPluginInstalled('Survey')) {
            return array();
        }

        $members = $this->findUserSurveys($userId);
        if (empty($members)) {
            return array();
        }

        $members = ArrayToolkit::index($members, 'surveyId');
        $surveyIds = ArrayToolkit::column($members, 'surveyId');
        $effectiveSurveys = $this->findUserEffectiveSurveys($surveyIds);
        $questionnaireIds = ArrayToolkit::column($effectiveSurveys, 'questionnaireId');
        $questionnaires = $this->getQuestionnaireService()->findQuestionnairesByIds($questionnaireIds);
        $questionnaires = ArrayToolkit::index($questionnaires, 'id');
        $surveys = array();

        foreach ($effectiveSurveys as $key => $effectiveSurvey) {
            $questionnaireId = $effectiveSurvey['questionnaireId'];
            $surveyId = $effectiveSurvey['id'];
            $surveys[] = array(
                'type' => 'survey',
                'id' => $effectiveSurvey['id'],
                'title' => $effectiveSurvey['name'],
                'description' => empty($questionnaires[$questionnaireId]['description']) ? '' : $questionnaires[$questionnaireId]['description'],
                'startTime' => $effectiveSurvey['startTime'],
                'endTime' => $effectiveSurvey['endTime'],
                'isResultVisible' => $effectiveSurvey['isResultVisible'],
                'joinTime' => empty($members[$surveyId]) ? 0 : $members[$surveyId]['createdTime'],
                'memberStatus' => $members[$surveyId]['status'],
                'surveyStatus' => $this->getAssignmentTimeStatus($effectiveSurvey['startTime'], $effectiveSurvey['endTime']),
            );
        }

        return $surveys;
    }

    protected function sortAssignments($assignments)
    {
        $joinTime = array();

        foreach ($assignments as $key => $assignment) {
            $joinTime[$key] = $assignment['joinTime'];
        }

        array_multisort($joinTime, SORT_DESC, $assignments);
    }

    protected function findUserProjectPlans($userId)
    {
        return $this->getProjectPlanMemberService()->searchProjectPlanMembers(
            array('userId' => $userId),
            array('id' => 'DESC'),
            0,
            PHP_INT_MAX
        );
    }

    protected function findUserOfflineActivities($userId)
    {
        return $this->getOfflineActivityMemberService()->searchMembers(
            array('userId' => $userId),
            array('id' => 'DESC'),
            0,
            PHP_INT_MAX
        );
    }

    protected function findUserEffectiveActivities($activityIds)
    {
        return $this->getOfflineActivityService()->searchOfflineActivities(
            array('ids' => $activityIds, 'endDate_GE' => time(), 'status' => 'published'),
            array('id' => 'DESC'),
            0,
            PHP_INT_MAX
        );
    }

    protected function findUserSurveys($userId)
    {
        return $this->getSurveyMemberService()->searchMembers(
            array('userId' => $userId),
            array('id' => 'DESC'),
            0,
            PHP_INT_MAX
        );
    }

    protected function findUserEffectiveSurveys($surveyIds)
    {
        $surveys = $this->getSurveyService()->searchSurveys(
            array('ids' => $surveyIds, 'status' => 'published', 'type' => 'questionnaire'),
            array('id' => 'DESC'),
            0,
            PHP_INT_MAX
        );

        $effectiveSurveys = array();
        foreach ($surveys as $survey) {
            if (!$this->isSurveyExpire($survey)) {
                $effectiveSurveys[] = $survey;
            }
        }

        return $effectiveSurveys;
    }

    protected function isSurveyExpire($survey)
    {
        if (!empty($survey['startTime']) && !empty($survey['endTime'])) {
            if (time() <= $survey['endTime']) {
                return false;
            }

            return true;
        }

        return false;
    }

    protected function findUserExams($userId)
    {
        return $this->getExamService()->searchMembers(
            array('userId' => $userId),
            array('id' => 'DESC'),
            0,
            PHP_INT_MAX
        );
    }

    protected function findUserEffectiveExams($examIds)
    {
        return $this->getExamService()->searchExams(
            array('ids' => $examIds,  'endTime_GT' => time(), 'status' => 'published', 'projectPlanId' => 0),
            array('id' => 'DESC'),
            0,
            PHP_INT_MAX
        );
    }

    protected function getRemainingResitTimes($member, $exam)
    {
        if ('fullMark' === $exam['type'] || empty($exam['resitTimes'])) {
            return 0;
        }

        return $exam['resitTimes'] - $member['finishedCount'];
    }

    protected function getAssignmentTimeStatus($startTime, $endTime)
    {
        if (empty($startTime)) {
            return 'ongoing';
        }

        $currentTime = time();
        if ($currentTime < $startTime) {
            return 'notStart';
        }

        if ($startTime <= $currentTime && $currentTime <= $endTime) {
            return 'ongoing';
        }

        return 'expired';
    }

    protected function transformCover($cover, $defaultCover)
    {
        $cover['small'] = AssetHelper::getFurl(empty($cover['small']) ? '' : $cover['small'], $defaultCover);
        $cover['middle'] = AssetHelper::getFurl(empty($cover['middle']) ? '' : $cover['middle'], $defaultCover);
        $cover['large'] = AssetHelper::getFurl(empty($cover['large']) ? '' : $cover['large'], $defaultCover);

        return $cover;
    }

    /**
     * @return OfflineActivityService
     */
    protected function getOfflineActivityService()
    {
        return $this->service('CorporateTrainingBundle:OfflineActivity:OfflineActivityService');
    }

    /**
     * @return MemberService
     */
    protected function getOfflineActivityMemberService()
    {
        return $this->service('CorporateTrainingBundle:OfflineActivity:MemberService');
    }

    /**
     * @return CategoryService
     */
    protected function getCategoryService()
    {
        return $this->service('CorporateTrainingBundle:Taxonomy:CategoryService');
    }

    /**
     * @return SurveyMemberService
     */
    protected function getSurveyMemberService()
    {
        return $this->service('SurveyPlugin:Survey:SurveyMemberService');
    }

    /**
     * @return SurveyService
     */
    protected function getSurveyService()
    {
        return $this->service('SurveyPlugin:Survey:SurveyService');
    }

    /**
     * @return QuestionnaireService
     */
    protected function getQuestionnaireService()
    {
        return $this->service('SurveyPlugin:Questionnaire:QuestionnaireService');
    }

    /**
     * @return ExamService
     */
    protected function getExamService()
    {
        return $this->service('ExamPlugin:Exam:ExamService');
    }

    /**
     * @return TestPaperService
     */
    protected function getTestPaperService()
    {
        return $this->service('ExamPlugin:TestPaper:TestPaperService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\Impl\MemberServiceImpl
     */
    protected function getProjectPlanMemberService()
    {
        return $this->service('CorporateTrainingBundle:ProjectPlan:MemberService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\Impl\ProjectPlanServiceImpl
     */
    protected function getProjectPlanService()
    {
        return $this->service('CorporateTrainingBundle:ProjectPlan:ProjectPlanService');
    }
}
