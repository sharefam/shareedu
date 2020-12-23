<?php

namespace CorporateTrainingBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use CorporateTrainingBundle\Biz\OfflineActivity\Service\Impl\OfflineActivityServiceImpl;
use CorporateTrainingBundle\Biz\PostCourse\Service\Impl\PostCourseServiceImpl;
use CorporateTrainingBundle\Biz\ProjectPlan\Service\Impl\MemberServiceImpl;
use CorporateTrainingBundle\Biz\ProjectPlan\Service\Impl\ProjectPlanServiceImpl;
use ExamPlugin\Biz\Exam\Service\Impl\ExamServiceImpl;

class MeTrainingRecord extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $currentUser = $this->getCurrentUser();
        $projectPlan = $this->buildProjectPlan($currentUser['id']);
        $postCourse = $this->buildPostCourse($currentUser['id'], $currentUser['postId']);
        if ($this->isPluginInstalled('Exam')) {
            $exam = $this->buildExam($currentUser['id']);
        }
        $exam = isset($exam) ? $exam : array();
        $offlineActivity = $this->buildOfflineActivity($currentUser['id']);

        $assignRecord = array_merge($projectPlan, $postCourse, $exam, $offlineActivity);

        return $assignRecord;
    }

    protected function buildProjectPlan($userId)
    {
        $userProjectPlanNum = $this->getProjectPlanMemberService()->countProjectPlanMembers(array('userId' => $userId));
        $userUnFinishedProjectPlanNum = $this->getProjectPlanService()->countUnfinishedProjectPlansByCurrentUserId();
        $userFinishedProjectPlanNum = $userProjectPlanNum - $userUnFinishedProjectPlanNum;
        $projectPlan[] = array(
            'type' => 'projectPlan',
            'assignNum' => $userProjectPlanNum,
            'finishedNum' => ($userFinishedProjectPlanNum > 0) ? $userFinishedProjectPlanNum : 0,
        );

        return $projectPlan;
    }

    protected function buildPostCourse($userId, $postId)
    {
        $userPostCourseNum = $this->getPostCourseService()->countPostCourses(array('postId' => $postId));
        $userFinishedPostCourseNum = $this->getPostCourseService()->countFinishedPostCoursesByPostIdAndUserId($postId, $userId);
        $postCourse[] = array(
            'type' => 'postCourse',
            'assignNum' => $userPostCourseNum,
            'finishedNum' => $userFinishedPostCourseNum,
        );

        return $postCourse;
    }

    protected function buildExam($userId)
    {
        $exams = $this->getExamService()->searchExams(array('projectPlanId' => 0), array('id' => 'DESC'), 0, PHP_INT_MAX);
        $examIds = ArrayToolkit::column($exams, 'id');
        $userExamNum = $this->getExamService()->countMembers(array('userId' => $userId, 'examIds' => $examIds));
        $userFinishedExamNum = $this->getExamService()->countMembers(array('userId' => $userId, 'passStatus' => 'passed', 'examIds' => $examIds));
        $exam[] = array(
            'type' => 'exam',
            'assignNum' => $userExamNum,
            'finishedNum' => $userFinishedExamNum,
        );

        return $exam;
    }

    protected function buildOfflineActivity($userId)
    {
        $userOfflineActivityNum = $this->getOfflineActivityMemberService()->countMembers(array('userId' => $userId));
        $userFinishedOfflineActivityNum = $this->getOfflineActivityMemberService()->countMembers(array('userId' => $userId, 'passedStatus' => 'passed'));
        $offlineActivity[] = array(
            'type' => 'offlineActivity',
            'assignNum' => $userOfflineActivityNum,
            'finishedNum' => $userFinishedOfflineActivityNum,
        );

        return $offlineActivity;
    }

    /**
     * @return ProjectPlanServiceImpl
     */
    protected function getProjectPlanService()
    {
        return $this->service('CorporateTrainingBundle:ProjectPlan:ProjectPlanService');
    }

    /**
     * @return MemberServiceImpl
     */
    protected function getProjectPlanMemberService()
    {
        return $this->service('CorporateTrainingBundle:ProjectPlan:MemberService');
    }

    /**
     * @return PostCourseServiceImpl
     */
    protected function getPostCourseService()
    {
        return $this->service('CorporateTrainingBundle:PostCourse:PostCourseService');
    }

    /**
     * @return ExamServiceImpl
     */
    protected function getExamService()
    {
        return $this->service('ExamPlugin:Exam:ExamService');
    }

    /**
     * @return OfflineActivityServiceImpl
     */
    protected function getOfflineActivityService()
    {
        return $this->service('CorporateTrainingBundle:OfflineActivity:OfflineActivityService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineActivity\Service\Impl\MemberServiceImpl
     */
    protected function getOfflineActivityMemberService()
    {
        return $this->service('CorporateTrainingBundle:OfflineActivity:MemberService');
    }
}
