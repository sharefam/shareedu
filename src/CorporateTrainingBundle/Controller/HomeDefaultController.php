<?php

namespace CorporateTrainingBundle\Controller;

use Biz\Group\Service\Impl\ThreadServiceImpl;
use CorporateTrainingBundle\Biz\OfflineActivity\Service\OfflineActivityService;
use CorporateTrainingBundle\Biz\ProjectPlan\Service\MemberService;
use CorporateTrainingBundle\Biz\ProjectPlan\Service\ProjectPlanService;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\BaseController;

class HomeDefaultController extends BaseController
{
    public function indexAction(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!empty($user)) {
            $user['courseLearningCount'] = $this->getCourseService()->countUserLearningCourses($user['id']);
            $user['courseCount'] = $this->getCourseService()->countUserLearnCourse($user['id']);
            $user['threadCount'] = $this->getThreadNum($user['id']);
            $user['learnTime'] = $this->getTaskResultService()->sumLearnTimeByUserId($user['id']);

            $this->getBatchNotificationService()->checkoutBatchNotification($user['id']);
        }
        $user['taskCount'] = $this->getTaskCount();

        $conditions = $request->query->all();

        $conditions['status'] = 'published';

        $conditions['ids'] = $this->getResourceVisibleScopeService()->findDepartmentVisibleResourceIdsByResourceTypeAndUserId('courseSet', $this->getCurrentUser()->getId());
        $courseSets = $this->getCourseSetService()->searchCourseSets(
            $conditions,
            array('recommendedSeq' => 'ASC', 'recommendedTime' => 'DESC', 'createdTime' => 'DESC'),
            null,
            null
        );
        $courseSets = array_slice($courseSets, 0, 4);

        //小组模块获取小组
        $activeGroup = $this->getGroupService()->searchGroups(array('status' => 'open'), array('memberNum' => 'DESC'),
            0, 8);

        $offlineActivityConditions = array(
            'status' => 'published',
            'enrollmentEndDate_GE' => time(),
            'ids' => $this->getResourceVisibleScopeService()->findVisibleResourceIdsByResourceTypeAndUserId('offlineActivity', $this->getCurrentUser()->getId()),
        );

        $offlineActivities = $this->getOfflineActivityService()->searchOfflineActivities(
            $offlineActivityConditions,
            array('startTime' => 'DESC'),
            0,
            12
        );

        return $this->render(
            'default/index.html.twig',
            array(
                'time' => time(),
                'user' => $user,
                'courseSets' => $courseSets,
                'activeGroup' => $activeGroup,
                'offlineActivities' => $offlineActivities,
            )
        );
    }

    protected function getTaskCount()
    {
        $user = $this->getCurrentUser();

        $allPostCourseCount = $this->getPostCourseService()->countPostCourses(
            array('postId' => $user['postId'])
        );
        $finishedPostCourseNum = $this->getPostCourseService()->countFinishedPostCoursesByPostIdAndUserId($user['postId'], $user['id']);
        $postCourseCount = $allPostCourseCount - $finishedPostCourseNum;

        $projectPlanTrainingCount = $this->getProjectPlanService()->countUnfinishedProjectPlansByCurrentUserId();

        $surveyCount = 0;
        if ($this->isPluginInstalled('Survey')) {
            $surveyCount = $this->getSurveyMemberService()->countUnfinishedSurveyByUserIdAndType($user['id'], 'questionnaire');
        }

        $examCount = 0;
        if ($this->isPluginInstalled('Exam')) {
            $examCount = $this->getExamService()->getUserUnfinishedExamNumWithProjectPlanId($user['id'], 0);
        }

        return $postCourseCount + $projectPlanTrainingCount + $surveyCount + $examCount;
    }

    protected function getOrgCodes($orgCodes)
    {
        if (count($orgCodes) > 1 && in_array('1.', $orgCodes)) {
            foreach ($orgCodes as $key => $orgCode) {
                if ('1.' == $orgCode) {
                    unset($orgCodes[$key]);
                    break;
                }
            }
        }

        return $orgCodes;
    }

    /**
     * @return ProjectPlanService
     */
    protected function getProjectPlanService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:ProjectPlanService');
    }

    /**
     * @return MemberService
     */
    protected function getProjectPlanMemberService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:MemberService');
    }

    /**
     * @return BatchNotificationService
     */
    protected function getBatchNotificationService()
    {
        return $this->getBiz()->service('User:BatchNotificationService');
    }

    protected function getThreadNum($userId)
    {
        $threadNum = $this->getThreadService()->countThreads(array('userId' => $userId));
        $threadPostNum = $this->getThreadService()->searchThreadPostsCount(array('userId' => $userId));

        return (int) $threadNum + (int) $threadPostNum[0]['count'];
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return ThreadServiceImpl
     */
    protected function getThreadService()
    {
        return $this->createService('Course:ThreadService');
    }

    protected function getTaskResultService()
    {
        return $this->createService('CorporateTrainingBundle:Task:TaskResultService');
    }

    protected function getPostCourseService()
    {
        return $this->createService('CorporateTrainingBundle:PostCourse:PostCourseService');
    }

    protected function getOrgService()
    {
        return $this->createService('Org:OrgService');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    protected function getGroupService()
    {
        return $this->createService('Group:GroupService');
    }

    /**
     * @return OfflineActivityService
     */
    protected function getOfflineActivityService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineActivity:OfflineActivityService');
    }

    protected function getSurveyMemberService()
    {
        return $this->createService('SurveyPlugin:Survey:SurveyMemberService');
    }

    protected function getExamService()
    {
        return $this->createService('ExamPlugin:Exam:ExamService');
    }

    protected function getResourceVisibleScopeService()
    {
        return $this->createService('ResourceScope:ResourceVisibleScopeService');
    }
}
