<?php

namespace CorporateTrainingBundle\Controller\StudyCenter;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\DeviceToolkit;
use AppBundle\Common\Paginator;
use CorporateTrainingBundle\Biz\Course\Service\Impl\CourseSetServiceImpl;
use CorporateTrainingBundle\Biz\DataStatistics\Service\DataStatisticsService;
use CorporateTrainingBundle\Biz\OfflineActivity\Service\OfflineActivityService;
use CorporateTrainingBundle\Biz\OfflineCourse\Service\TaskService;
use CorporateTrainingBundle\Biz\PostCourse\Service\PostCourseService;
use CorporateTrainingBundle\Biz\ProjectPlan\Service\ProjectPlanService;
use CorporateTrainingBundle\Common\DateToolkit;
use ExamPlugin\Biz\Exam\Service\Impl\ExamServiceImpl;
use SurveyPlugin\Biz\Survey\Service\SurveyMemberService;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\BaseController;
use CorporateTrainingBundle\Common\OrgToolkit;

class H5Controller extends BaseController
{
    public function myStudyCenterAction(Request $request)
    {
        if (!$this->isMobileClient()) {
            return $this->forward('CorporateTrainingBundle:StudyCenter/MyTask:projectPlanList', array());
        }

        $user = $this->getUserService()->getUser($this->getCurrentUser()->getId());
        list($projectPlanCount, $postCourseCount, $examCount, $surveyCount) = $this->getTaskCount();
        $orgs = $this->getOrgService()->findOrgsByIds($user['orgIds']);
        $orgNames = OrgToolkit::buildOrgsNames($user['orgIds'], $orgs);

        return $this->render(
            '@CorporateTraining/study-center/h5/my-study-center.html.twig',
            array(
                'taskCount' => $projectPlanCount + $postCourseCount + $examCount + $surveyCount,
                'lastYearTime' => $this->getUserLearnData('last'),
                'thisYearTime' => $this->getUserLearnData('this'),
                'totalTime' => $this->getUserLearnData('all'),
                'orgNames' => $orgNames,
            )
        );
    }

    public function myTasksAction(Request $request)
    {
        if (!$this->isMobileClient()) {
            return $this->forward('CorporateTrainingBundle:StudyCenter/MyTask:projectPlanList', array());
        }

        list($projectPlanCount, $postCourseCount, $examCount, $surveyCount) = $this->getTaskCount();

        return $this->render(
            '@CorporateTraining/study-center/h5/my-tasks.html.twig',
            array(
                'projectPlanCount' => $projectPlanCount,
                'postCourseCount' => $postCourseCount,
                'examCount' => $examCount,
                'surveyCount' => $surveyCount,
            )
        );
    }

    public function myDetailAction(Request $request)
    {
        if (!$this->isMobileClient()) {
            return $this->forward('CorporateTrainingBundle:StudyRecord:projectPlanRecord', array('userId' => $this->getCurrentUser()->getId()));
        }

        $user = $this->getUserService()->getUser($this->getCurrentUser()->getId());
        $orgs = $this->getOrgService()->findOrgsByIds($user['orgIds']);
        $orgNames = OrgToolkit::buildOrgsNames($user['orgIds'], $orgs);

        return $this->render(
            '@CorporateTraining/study-center/h5/my.html.twig',
            array(
                'orgNames' => $orgNames,
            )
        );
    }

    public function myStudyRecordAction(Request $request)
    {
        if (!$this->isMobileClient()) {
            return $this->forward('CorporateTrainingBundle:StudyRecord:projectPlanRecord', array('userId' => $this->getCurrentUser()->getId()));
        }

        $user = $this->getUserService()->getUser($this->getCurrentUser()->getId());
        $orgs = $this->getOrgService()->findOrgsByIds($user['orgIds']);
        $orgNames = OrgToolkit::buildOrgsNames($user['orgIds'], $orgs);

        return $this->render(
            '@CorporateTraining/study-center/h5/my-study-record.html.twig',
            array(
                'lastYearTime' => $this->getUserLearnData('last'),
                'thisYearTime' => $this->getUserLearnData('this'),
                'totalTime' => $this->getUserLearnData('all'),
                'orgNames' => $orgNames,
            )
        );
    }

    public function favoritedAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $user = $this->getUserService()->getUser($user->getId());
        $userProfile = $this->getUserService()->getUserProfile($user['id']);
        $userProfile['about'] = strip_tags($userProfile['about'], '');
        $userProfile['about'] = preg_replace('/ /', '', $userProfile['about']);
        $user = array_merge($user, $userProfile);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseSetService()->countUserFavorites($user['id']),
            20
        );

        $favorites = $this->getCourseSetService()->searchUserFavorites(
            $user['id'], $paginator->getOffsetCount(), $paginator->getPerPageCount()
        );
        $courseSetIds = ArrayToolkit::column($favorites, 'courseSetId');
        $courseSets = $this->getCourseSetService()->findCourseSetsByIds($courseSetIds);

        return $this->render('@CorporateTraining/study-center/h5/courses_favorited.html.twig', array(
            'user' => $user,
            'courseFavorites' => $courseSets,
            'paginator' => $paginator,
            'type' => 'favorited',
        ));
    }

    protected function isMobileClient()
    {
        return DeviceToolkit::isMobileClient();
    }

    protected function getUserLearnData($type)
    {
        $userId = $this->getCurrentUser()->getId();

        switch ($type) {
           case 'last':
               list($startDateTime, $endDateTime) = DateToolkit::generateStartDateAndEndDate('year', -1, 'time');
               $date = array(
                   'startDateTime' => $startDateTime,
                   'endDateTime' => $endDateTime,
               );
               break;
           case 'this':
               list($startDateTime, $endDateTime) = DateToolkit::generateStartDateAndEndDate('year', 0, 'time');
               $date = array(
                   'startDateTime' => $startDateTime,
                   'endDateTime' => $endDateTime,
               );
               break;
           default:
               $date = array(
                   'startDateTime' => 0,
                   'endDateTime' => time(),
               );
               break;
       }
        $onlineCourse = $this->getDataStatisticsService()->getOnlineStudyHoursLearnDataForUserLearnDataExtension(array('userIds' => array($userId), 'date' => $date));
        $onlineTime = empty($onlineCourse[$userId]) ? 0 : $onlineCourse[$userId];
        $offlineCourse = $this->getOfflineCourseTaskService()->getOfflineStudyHoursLearnDataForUserLearnDataExtension(array('userIds' => array($userId), 'date' => $date));
        $offlineTime = empty($offlineCourse[$userId]) ? 0 : $offlineCourse[$userId];

        return array('onlineTime' => $onlineTime, 'offlineTime' => $offlineTime);
    }

    protected function getTaskCount()
    {
        $user = $this->getCurrentUser();

        $allPostCourseCount = $this->getPostCourseService()->countPostCourses(
            array('postId' => $user['postId'])
        );
        $finishedPostCourseNum = $this->getPostCourseService()->countFinishedPostCoursesByPostIdAndUserId($user['postId'], $user['id']);
        $postCourseCount = $allPostCourseCount - $finishedPostCourseNum;

        $projectPlanCount = $this->getProjectPlanService()->countUnfinishedProjectPlansByCurrentUserId();

        $surveyCount = 0;
        if ($this->isPluginInstalled('Survey')) {
            $surveyCount = $this->getSurveyMemberService()->countUnfinishedSurveyByUserIdAndType($user['id'], 'questionnaire');
        }

        $examCount = 0;
        if ($this->isPluginInstalled('Exam')) {
            $examCount = $this->getExamService()->getUserUnfinishedExamNumWithProjectPlanId($user['id'], 0);
        }

        return array($projectPlanCount, $postCourseCount, $examCount, $surveyCount);
    }

    /**
     * @return DataStatisticsService
     */
    protected function getDataStatisticsService()
    {
        return $this->createService('CorporateTrainingBundle:DataStatistics:DataStatisticsService');
    }

    /**
     * @return TaskService
     */
    protected function getOfflineCourseTaskService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineCourse:TaskService');
    }

    /**
     * @return ProjectPlanService
     */
    protected function getProjectPlanService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:ProjectPlanService');
    }

    /**
     * @return PostCourseService
     */
    protected function getPostCourseService()
    {
        return $this->createService('CorporateTrainingBundle:PostCourse:PostCourseService');
    }

    /**
     * @return CourseSetServiceImpl
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return OfflineActivityService
     */
    protected function getOfflineActivityService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineActivity:OfflineActivityService');
    }

    /**
     * @return SurveyMemberService
     */
    protected function getSurveyMemberService()
    {
        return $this->createService('SurveyPlugin:Survey:SurveyMemberService');
    }

    /**
     * @return ExamServiceImpl
     */
    protected function getExamService()
    {
        return $this->createService('ExamPlugin:Exam:ExamService');
    }
}
