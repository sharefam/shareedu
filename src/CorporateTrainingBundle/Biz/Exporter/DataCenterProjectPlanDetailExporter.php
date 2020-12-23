<?php

namespace CorporateTrainingBundle\Biz\Exporter;

use AppBundle\Common\ArrayToolkit;

class DataCenterProjectPlanDetailExporter extends AbstractExporter
{
    public function canExport($parameters)
    {
        $user = $this->biz['user'];

        if (!$user->hasPermission('admin_data_center_project_plan')) {
            return false;
        }

        return true;
    }

    public function getExportFileName()
    {
        return 'data_project_plan_detail.xls';
    }

    public function getSortedHeadingRow($parameters)
    {
        $row = array(
            array('code' => 'name', 'title' => $this->trans('project_plan.name')),
            array('code' => 'date', 'title' => $this->trans('project_plan.project_plan_date')),
            array('code' => 'org', 'title' => $this->trans('project_plan.org')),
            array('code' => 'categoryName', 'title' => $this->trans('project_plan.classification')),
            array('code' => 'coursesNum', 'title' => $this->trans('admin.data_center.project_plan.detail.courses_num')),
            array('code' => 'status', 'title' => $this->trans('admin.data_center.project_plan.detail.status')),
            array('code' => 'offlineCoursesHours', 'title' => $this->trans('admin.data_center.project_plan.detail.offline_course.hours')),
            array('code' => 'membersNum', 'title' => $this->trans('admin.data_center.project_plan.detail.members_num')),
            array('code' => 'progress', 'title' => $this->trans('project_plan.finishing_rate')),
        );

        if ($this->isPluginInstalled('Survey')) {
            $row[] = array('code' => 'averageScore', 'title' => $this->trans('admin.data_center.project_plan.detail.survey'));
        }

        return $row;
    }

    public function buildExportData($parameters)
    {
        $date = explode('-', $parameters['dataSearchTime']);
        $dataSearchTime['startDateTime'] = strtotime($date[0]);
        $dataSearchTime['endDateTime'] = strtotime($date[1].' 23:59:59');
        $parameters = $this->fillOrgCode($parameters);
        if (!empty($parameters['status']) && 'all' === $parameters['status']) {
            unset($parameters['status']);
            $parameters['excludeStatus'] = array('unpublished');
        }

        $projectPlans = $this->getProjectPlanService()->searchProjectPlans($parameters, array(), 0, PHP_INT_MAX);
        $projectIds = ArrayToolkit::column($projectPlans, 'id');
        $projectIds = empty($projectIds) ? array(-1) : $projectIds;
        $projectPlans = $this->getProjectPlanService()->findProjectPlansByDateAndIds($dataSearchTime, $projectIds, 0, count($projectIds));

        $projectsDetails = $this->handleProjectsDetails($projectPlans);

        $projectData = array();
        foreach ($projectsDetails as $projectsDetail) {
            $data = array(
                'name' => $projectsDetail['name'],
                'date' => $projectsDetail['date'],
                'org' => $projectsDetail['org'],
                'categoryName' => $projectsDetail['categoryName'],
                'coursesNum' => $this->trans('admin.data_center.project_plan.detail.onlineCoursesNum').$projectsDetail['onlineCoursesNum'].'/'.'admin.data_center.project_plan.detail.offlineCoursesNum'.$projectsDetail['offlineCoursesNum'],
                'status' => $projectsDetail['status'],
                'offlineCoursesHours' => $projectsDetail['offlineCoursesHours'],
                'membersNum' => $projectsDetail['membersNum'],
                'progress' => $projectsDetail['progress'],
            );

            if ($this->isPluginInstalled('Survey')) {
                $data['averageScore'] = ($projectsDetail['averageScore']).'/5.00';
            }
            $projectData[] = $data;
        }

        $exportData[] = array(
            'data' => $projectData,
        );

        return $exportData;
    }

    protected function handleProjectsDetails($projectPlans)
    {
        foreach ($projectPlans as &$projectPlan) {
            $org = $this->getOrgService()->getOrgByOrgCode($projectPlan['orgCode']);
            $projectPlan['org'] = $org['name'].':'.$org['code'];
            $category = $this->getCategoryService()->getCategory($projectPlan['categoryId']);
            $projectPlan['date'] = date('Y-m-d', $projectPlan['startTime']).'-'.date('Y-m-d', $projectPlan['endTime']);
            $projectPlan['categoryName'] = $category['name'];
            $projectPlan['onlineCoursesNum'] = $this->getProjectPlanService()->countProjectPlanItems(array('projectPlanId' => $projectPlan['id'], 'targetType' => 'course'));
            $projectPlan['offlineCoursesNum'] = $this->getProjectPlanService()->countProjectPlanItems(array('projectPlanId' => $projectPlan['id'], 'targetType' => 'offline_course'));
            $projectPlan['status'] = $this->getProjectPlanStatus($projectPlan['status']);
            $projectPlan['offlineCoursesHours'] = $this->getOfflineCoursesHours($projectPlan['id']);

            $projectPlan['membersNum'] = $this->getProjectPlanMemberService()->countProjectPlanMembers(array('projectPlanId' => $projectPlan['id']));

            $projectPlan['progress'] = $this->getProjectPlanService()->getProjectPlanProgress($projectPlan['id']).'%';

            if ($this->isPluginInstalled('Survey')) {
                $projectPlan['averageScore'] = $this->getProjectPlanAverageScore($projectPlan['id']);
            }
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

        return round(0 == count($courses) ? 0 : (array_sum($coursesAverageScore) / count($courses)), 2);
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
            $course['recoverySurveyNum'] = empty($surveyIds) || empty($userIds) ? 0 : $this->getSurveyResultService()->countSurveyResults(array('surveyIds' => $surveyIds, 'status' => 'finished', 'userIds' => $userIds));
            $sumScore = $this->getSurveyResultService()->sumScoreBySurveyIdsAndUserIds($surveyIds, $userIds);
            $averageScore = (0 != $course['recoverySurveyNum']) ? $sumScore / $course['recoverySurveyNum'] : 0;
            $course['averageScore'] = sprintf('%.2f', $averageScore);
        }

        return $courses;
    }

    /**
     * @return \Biz\Taxonomy\Service\Impl\CategoryServiceImpl
     */
    protected function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
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

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineCourse\Service\OfflineCourseService
     */
    protected function getOfflineCourseService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineCourse:OfflineCourseService');
    }

    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    protected function getSurveyService()
    {
        return $this->createService('SurveyPlugin:Survey:SurveyService');
    }

    protected function getSurveyResultService()
    {
        return $this->createService('SurveyPlugin:Survey:SurveyResultService');
    }
}
