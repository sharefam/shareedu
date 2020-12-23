<?php

namespace CorporateTrainingBundle\Controller\Admin\Data;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use CorporateTrainingBundle\Common\DateToolkit;

class ProjectPlanDataController extends BaseController
{
    public function projectPlanDataOverviewAction(Request $request)
    {
        $fields = $request->query->all();
        $year = !empty($fields['year']) ? $fields['year'] : date('Y');
        $fields = $this->fillOrgCode($fields);
        if (empty($fields['likeOrgCode'])) {
            $fields['likeOrgCode'] = '1.';
        }

        if (empty($fields['categoryId'])) {
            $fields['categoryId'] = '';
        }

        $projectPlansNum = array();
        $projectPlanMembersNum = array();
        for ($month = 1; $month <= 12; ++$month) {
            $monthStartDate = date($year.'-'.$month.'-'. 01);
            $monthEndDate = date($year.'-'.$month.'-'.'t');

            $date['monthStartTime'] = strtotime($monthStartDate);
            $date['monthEndTime'] = strtotime($monthEndDate.' 23:59:59');

            list($monthlyProjectPlansNum, $monthlyProjectPlanMembersNum) = $this->getProjectPlanService()->getMonthlyProjectPlansNumAndMembersNumByOrgAndCategory($date, $fields['likeOrgCode'], $fields['categoryId']);

            $projectPlansNum = array_merge($projectPlansNum, array($monthlyProjectPlansNum));
            $projectPlanMembersNum = array_merge($projectPlanMembersNum, array($monthlyProjectPlanMembersNum));
        }

        $chartData = array(
            'series' => array(
                'projectPlansNum' => $projectPlansNum,
                'projectPlanMembersNum' => $projectPlanMembersNum,
            ),
        );

        return $this->render('admin/data/projectPlan/overview.html.twig', array(
            'year' => $year,
            'chartData' => json_encode($chartData),
        ));
    }

    public function projectPlanDataDetailAction(Request $request)
    {
        $fields = $request->query->all();
        list($paginator, $projectsDetails, $dataSearchTime) = $this->buildProjectPlanPaginatorData($fields);

        return $this->render('admin/data/projectPlan/detail.html.twig', array(
            'paginator' => $paginator,
            'projectsDetails' => json_encode($projectsDetails),
            'dataSearchTime' => $dataSearchTime,
        ));
    }

    public function projectPlanDataAjaxDetailAction(Request $request)
    {
        $fields = $request->request->all();
        list($paginator, $projectsDetails, $dataSearchTime) = $this->buildProjectPlanPaginatorData($fields, 'ajax');

        return $this->render('admin/data/projectPlan/detail-tr.html.twig', array(
            'paginator' => $paginator,
            'projectsDetails' => json_encode($projectsDetails),
        ));
    }

    protected function buildProjectPlanPaginatorData($fields, $type = '')
    {
        $fields = $this->fillOrgCode($fields);
        if (!empty($fields['status']) && 'all' === $fields['status']) {
            unset($fields['status']);
        }
        $fields['excludeStatus'] = array('unpublished');
        $projectPlans = $this->getProjectPlanService()->searchProjectPlans($fields, array(), 0, PHP_INT_MAX);
        $projectIds = empty($projectPlans) ? array(-1) : ArrayToolkit::column($projectPlans, 'id');

        $dataSearchTime = array();
        if (empty($fields['dataSearchTime'])) {
            list($startDateTime, $endDateTime) = DateToolkit::generateStartDateAndEndDate('year');
            $dataSearchTime['startDateTime'] = strtotime($startDateTime);
            $dataSearchTime['endDateTime'] = strtotime($endDateTime.' 23:59:59');
        } else {
            $date = explode('-', $fields['dataSearchTime']);
            $dataSearchTime['startDateTime'] = strtotime($date[0]);
            $dataSearchTime['endDateTime'] = strtotime($date[1].' 23:59:59');
        }

        $count = $this->getProjectPlanService()->countProjectPlansByDateAndIds($dataSearchTime, $projectIds);

        $paginator = new Paginator(
            $this->get('request'),
            $count,
            10
        );
        if ('ajax' != $type) {
            $paginator->setBaseUrl($this->generateUrl('admin_data_center_project_plan_ajax_detail'));
        }

        $projectPlans = $this->getProjectPlanService()->findProjectPlansByDateAndIds($dataSearchTime, $projectIds, $paginator->getOffsetCount(), $paginator->getPerPageCount());

        $projectsDetails = $this->handleProjectsDetails($projectPlans);

        return array($paginator, $projectsDetails, $dataSearchTime);
    }

    protected function handleProjectsDetails($projectPlans)
    {
        foreach ($projectPlans as &$projectPlan) {
            $org = $this->getOrgService()->getOrgByOrgCode($projectPlan['orgCode']);
            $projectPlan['org'] = array(
                'code' => $org['code'],
                'name' => $org['name'],
            );
            $category = $this->getCategoryService()->getCategory($projectPlan['categoryId']);
            $projectPlan['startTime'] = date('Y-m-d', $projectPlan['startTime']);
            $projectPlan['endTime'] = date('Y-m-d', $projectPlan['endTime']);
            $projectPlan['categoryName'] = $category['name'];
            $projectPlan['coverImgUri'] = $this->getCoverImgUri($projectPlan, 'middle', 'project-plan.png');
            $projectPlan['coursesNum'] = $this->getProjectPlanService()->countProjectPlanItems(array('projectPlanId' => $projectPlan['id'], 'targetType' => 'course'));
            $projectPlan['offlineCoursesNum'] = $this->getProjectPlanService()->countProjectPlanItems(array('projectPlanId' => $projectPlan['id'], 'targetType' => 'offline_course'));
            $projectPlan['status'] = $this->getProjectPlanStatus($projectPlan['status']);
            $projectPlan['offlineCoursesHours'] = $this->getOfflineCoursesHours($projectPlan['id']);

            $projectPlan['membersNum'] = $this->getProjectPlanMemberService()->countProjectPlanMembers(array('projectPlanId' => $projectPlan['id']));

            $projectPlan['progress'] = $this->getProjectPlanService()->getProjectPlanProgress($projectPlan['id']).'%';

            if ($this->isPluginInstalled('Survey')) {
                $projectPlan['averageScore'] = ($this->getProjectPlanAverageScore($projectPlan['id'])).'/5.00';
                $projectPlan['teacher_evaluate_list'] = $this->generateUrl('project_plan_teacher_evaluate_list', array('id' => $projectPlan['id']));
            }

            $projectPlan['memberList'] = $this->generateUrl('project_plan_study_data_list', array('projectPlanId' => $projectPlan['id']));
        }

        return $projectPlans;
    }

    public function getProjectPlanStatus($status)
    {
        switch ($status) {
            case 'unpublished':
                return $this->trans('project_plan.status.unpublished');
            case 'published':
                return $this->trans('project_plan.status.published');
            case 'closed':
                return $this->trans('project_plan.status.closed');
            case 'archived':
                return $this->trans('project_plan.status.archived');
            default:
                return '';
        }
    }

    protected function getCoverImgUri($projectPlan, $type, $defaultKey)
    {
        $coverPath = $this->get('web.twig.app_extension')->courseSetCover($projectPlan, $type);

        return $this->getWebExtension()->getFpath($coverPath, $defaultKey);
    }

    protected function getOfflineCoursesHours($projectPlanId)
    {
        $projectOfflineCourses = $this->getProjectPlanService()->findProjectPlanItemsByProjectPlanIdAndTargetType($projectPlanId, 'offline_course');

        $offlineLearnTime = 0;
        foreach ($projectOfflineCourses as $projectOfflineCourse) {
            $offlineCourseTasks = $this->getOfflineCourseTaskService()->searchTasks(array('offlineCourseId' => $projectOfflineCourse['targetId'], 'type' => 'offlineCourse'), array(), 0, PHP_INT_MAX);
            foreach ($offlineCourseTasks as $task) {
                $timeRange = $task['endTime'] - $task['startTime'];
                $offlineLearnTime += $timeRange;
            }
        }

        return round($offlineLearnTime / 3600, 2);
    }

    protected function getProjectPlanAverageScore($projectPlanId)
    {
        $projectPlanMembers = $this->getProjectPlanMemberService()->findMembersByProjectPlanId($projectPlanId);
        $userIds = empty($projectPlanMembers) ? array(-1) : ArrayToolkit::column($projectPlanMembers, 'userId');

        $projectPlanItemIds = $this->getProjectPlanService()->findHasFinishedSurveyResultProjectPlanItemIds($projectPlanId, $userIds);
        $projectPlanItemIds = empty($projectPlanItemIds) ? array(-1) : ArrayToolkit::column($projectPlanItemIds, 'id');

        $projectPlanItems = $this->getProjectPlanService()->findProjectPlanItemsByIds($projectPlanItemIds);
        $courses = array();
        if (!empty($projectPlanItems)) {
            foreach ($projectPlanItems as $key => $projectPlanItem) {
                if ('course' == $projectPlanItem['targetType']) {
                    $courses[$key] = $this->getCourseService()->getCourse($projectPlanItem['targetId']);
                } elseif ('offline_course' == $projectPlanItem['targetType']) {
                    $courses[$key] = $this->getOfflineCourseService()->getOfflineCourse($projectPlanItem['targetId']);
                }
                $courses[$key]['targetType'] = $projectPlanItem['targetType'];
            }
        }

        $courses = $this->getSurveyInfo($courses, $userIds);
        $coursesAverageScore = ArrayToolkit::column($courses, 'averageScore');

        return 0 == count($courses) ? '--' : sprintf('%.2f', round(array_sum($coursesAverageScore) / count($courses), 2));
    }

    protected function getSurveyInfo($courses, $userIds)
    {
        foreach ($courses as &$course) {
            if ('course' == $course['targetType']) {
                $mediaType = 'questionnaire';
            } elseif ('offline_course' == $course['targetType']) {
                $mediaType = 'offlineCourseQuestionnaire';
            }

            $activities = $this->getActivityService()->search(array('fromCourseId' => $course['id'], 'mediaType' => $mediaType), array(), 0, PHP_INT_MAX);

            $surveyIds = ArrayToolkit::column($activities, 'mediaId');
            $surveys = !empty($surveyIds) ? $this->getSurveyService()->searchSurveys(array('ids' => $surveyIds), array(), 0, PHP_INT_MAX) : array();
            $course['recoverySurveyNum'] = empty($surveyIds) || empty($userIds) ? 0 : $this->getSurveyResultService()->countSurveyResults(array('surveyIds' => $surveyIds, 'status' => 'finished', 'userIds' => $userIds));
            $surveys = $this->getAggregateAverageScore($surveys, $userIds);
            $sumScore = $this->getSurveyResultService()->sumScoreBySurveyIdsAndUserIds($surveyIds, $userIds);
            $averageScore = (0 != $course['recoverySurveyNum']) ? $sumScore / $course['recoverySurveyNum'] : 0;
            $course['averageScore'] = sprintf('%.2f', $averageScore);
        }

        return $courses;
    }

    protected function getAggregateAverageScore($surveys, $userIds)
    {
        foreach ($surveys as &$survey) {
            $surveyResultNum = $this->getSurveyResultService()->countSurveyResults(array('surveyId' => $survey['id'], 'userIds' => $userIds, 'status' => 'finished'));
            $survey['weightedAverage'] = $this->getSurveyResultService()->sumScoreBySurveyIdsAndUserIds(array($survey['id']), $userIds);
            $survey['weightedAverage'] = sprintf('%.2f', (0 != $surveyResultNum) ? $survey['weightedAverage'] / $surveyResultNum : 0.00);
        }

        return $surveys;
    }

    protected function getSurveyResultService()
    {
        return $this->createService('SurveyPlugin:Survey:SurveyResultService');
    }

    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\Impl\ProjectPlanServiceImpl
     */
    protected function getProjectPlanService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:ProjectPlanService');
    }

    /**
     * @return \Biz\Taxonomy\Service\Impl\CategoryServiceImpl
     */
    protected function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }

    protected function getOrgService()
    {
        return $this->createService('Org:OrgService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineCourse\Service\Impl\TaskServiceImpl
     */
    protected function getOfflineCourseTaskService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineCourse:TaskService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\Impl\MemberServiceImpl
     */
    protected function getProjectPlanMemberService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:MemberService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineCourse\Service\OfflineCourseService
     */
    protected function getOfflineCourseService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineCourse:OfflineCourseService');
    }

    protected function getSurveyService()
    {
        return $this->createService('SurveyPlugin:Survey:SurveyService');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
