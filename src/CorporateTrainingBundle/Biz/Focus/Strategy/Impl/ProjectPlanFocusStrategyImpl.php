<?php

namespace CorporateTrainingBundle\Biz\Focus\Strategy\Impl;

use AppBundle\Common\ArrayToolkit;
use CorporateTrainingBundle\Biz\Focus\Strategy\BaseFocusStrategy;
use CorporateTrainingBundle\Biz\Focus\Strategy\FocusStrategy;
use CorporateTrainingBundle\Biz\OfflineCourse\Service\OfflineCourseService;
use CorporateTrainingBundle\Biz\ProjectPlan\Service\ProjectPlanService;

class ProjectPlanFocusStrategyImpl extends BaseFocusStrategy implements FocusStrategy
{
    public function findFocusAgo($type = 'my', $time)
    {
        $startTime = mktime(0, 0, 0, date('m', $time), date('d', $time), date('Y', $time));
        $projectPlanIds = $this->findProjectPlanIds($type);

        if (empty($projectPlanIds)) {
            return array();
        }

        $conditions = array(
            'ids' => $projectPlanIds,
            'endTime_LT' => $time,
            'endTime_GE' => $startTime,
            'status' => 'published',
        );

        $projectPlans = $this->getProjectPlanService()->searchProjectPlans(
            $conditions,
            array('endTime' => 'DESC'),
            0,
            5
        );

        return $this->buildItems($projectPlans, $conditions, array('startTime' => 'ASC'));
    }

    public function findFocusNow($type = 'my', $time)
    {
        $projectPlanIds = $this->findProjectPlanIds($type);

        if (empty($projectPlanIds)) {
            return array();
        }

        $conditions = array(
            'ids' => $projectPlanIds,
            'startTime_LE' => $time,
            'endTime_GT' => $time,
            'status' => 'published',
        );

        $projectPlans = $this->getProjectPlanService()->searchProjectPlans(
            $conditions,
            array('endTime' => 'ASC'),
            0,
            PHP_INT_MAX
        );

        return $this->buildItems($projectPlans, $conditions, array('endTime' => 'ASC'));
    }

    public function findFocusLater($type = 'my', $time)
    {
        $endTime = mktime(0, 0, 0, date('m', $time), date('d', $time) + 1, date('Y', $time)) - 1;
        $projectPlanIds = $this->findProjectPlanIds($type);

        if (empty($projectPlanIds)) {
            return array();
        }

        $conditions = array(
            'ids' => $projectPlanIds,
            'startTime_GE' => $time,
            'startTime_LT' => $endTime,
            'status' => 'published',
        );

        $projectPlans = $this->getProjectPlanService()->searchProjectPlans(
            $conditions,
            array('startTime' => 'ASC'),
            0,
            5
        );

        return $this->buildItems($projectPlans, $conditions, array('startTime' => 'ASC'));
    }

    public function findFocusByStartTimeAndEndTime($type = 'my', $startTime, $endTime)
    {
        $projectPlanIds = $this->findProjectPlanIds($type);

        if (empty($projectPlanIds)) {
            return array();
        }

        $conditions = array(
            'ids' => $projectPlanIds,
            'endTime_GT' => $startTime,
            'startTime_LE' => $endTime,
            'status' => 'published',
        );

        return $this->getProjectPlanService()->searchProjectPlans(
            $conditions,
            array('id' => 'ASC'),
            0,
            PHP_INT_MAX
        );
    }

    protected function findProjectPlanIds($type)
    {
        $currentUser = $this->getCurrentUser();

        if (!$currentUser->hasPermission('admin_project_plan') && !$currentUser->hasPermission('admin_data') && !in_array('ROLE_TEACHER', $currentUser['roles'])) {
            $projectPlanIds = array();
        } else {
            $projectPlanIds = array_merge(
                $this->findMyCreateProjectPlanIds(),
                $this->findMyTeachingProjectPlanIds()
            );
        }

        return $projectPlanIds;
    }

    protected function findMyCreateProjectPlanIds()
    {
        $currentUser = $this->getCurrentUser();
        $conditions = array(
            'createdUserId' => $currentUser['id'],
            'status' => 'published',
        );

        $projectPlans = $this->getProjectPlanService()->searchProjectPlans(
            $conditions,
            array('id' => 'ASC'),
            0,
            PHP_INT_MAX
        );

        return ArrayToolkit::column($projectPlans, 'id');
    }

    protected function findMyTeachingProjectPlanIds()
    {
        $currentUser = $this->getCurrentUser();

        $myTeachingOfflineCourses = $this->getOfflineCourseService()->findTeachingOfflineCourseByUserId($currentUser['id']);

        return ArrayToolkit::column($myTeachingOfflineCourses, 'projectPlanId');
    }

    protected function buildItems($projectPlans, $conditions, $orderBy)
    {
        unset($conditions['ids']);
        foreach ($projectPlans as &$projectPlan) {
            $minStartTime = $projectPlan['startTime'];
            $minEndTime = $projectPlan['endTime'];
            $conditions['projectPlanId'] = $projectPlan['id'];
            $conditions['targetTypes'] = array('offline_course', 'offline_exam', 'exam');
            $projectPlanItems = $this->getProjectPlanService()->searchProjectPlanItems(
                $conditions,
                $orderBy,
                0,
                PHP_INT_MAX
            );

            foreach ($projectPlanItems as $key => $item) {
                if ('offline_course' == $item['targetType'] && 0 == $item['detail']['taskNum']) {
                    unset($projectPlanItems[$key]);
                } else {
                    if ($item['startTime'] < $minEndTime) {
                        $minStartTime = $item['startTime'];
                    }

                    if ($item['endTime'] < $minEndTime) {
                        $minEndTime = $item['endTime'];
                    }
                }
            }

            $projectPlan['items'] = $projectPlanItems;
            $projectPlan['startTime'] = $minStartTime;
            $projectPlan['endTime'] = $minEndTime;
        }

        return $projectPlans;
    }

    /**
     * @return ProjectPlanService
     */
    protected function getProjectPlanService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:ProjectPlanService');
    }

    /**
     * @return OfflineCourseService
     */
    protected function getOfflineCourseService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineCourse:OfflineCourseService');
    }
}
