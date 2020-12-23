<?php

namespace CorporateTrainingBundle\Biz\DefaultSearch;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;

class CourseSearch extends AbstractSearch
{
    public function search($request, $keywords)
    {
        $courseSetConditions = $this->prepareSearchConditions($keywords);

        $courseSetNum = $this->getCourseSetService()->countCourseSets($courseSetConditions);

        $paginator = new Paginator(
            $request,
            $courseSetNum,
            10
        );

        $courseSets = $this->getCourseSetService()->searchCourseSets(
            $courseSetConditions,
            array('updatedTime' => 'desc'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return array($courseSets, $paginator);
    }

    public function count($request, $keywords)
    {
        $courseSetConditions = $this->prepareSearchConditions($keywords);
        $courseSetNum = $this->getCourseSetService()->countCourseSets($courseSetConditions);

        return $courseSetNum;
    }

    protected function prepareSearchConditions($keywords)
    {
        $user = $this->getCurrentUser();
        $scopeCourseSetIds = $this->getResourceVisibleScopeService()->findVisibleResourceIdsByResourceTypeAndUserId('courseSet',
            $user['id']);

        //搜索任务
        $tasks = $this->getCourseTaskService()->searchTasks(
            array(
                'titleLike' => $keywords,
                'status' => 'published',
                'fromCourseSetIds' => $scopeCourseSetIds,
            ),
            array(),
            0,
            PHP_INT_MAX,
            array('fromCourseSetId')
        );

        $courseSetIdsFromTask = ArrayToolkit::column($tasks, 'fromCourseSetId');

        //搜索课程
        $courseSets = $this->getCourseSetService()->searchCourseSets(
            array(
                'ids' => $scopeCourseSetIds,
                'minCoursePrice' => '0.00',
                'status' => 'published',
                'title' => $keywords,
            ),
            array(),
            0,
            PHP_INT_MAX,
            array('id')
        );

        $courseSetIds = ArrayToolkit::column($courseSets, 'id');
        $courseSetIds = array_unique(array_merge($courseSetIds, $courseSetIdsFromTask));

        $courseSetConditions = array(
            'ids' => !empty($courseSetIds) ? $courseSetIds : array(-1),
        );

        return $courseSetConditions;
    }

    /**
     * @return \Biz\Course\Service\CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return \Biz\Task\Service\Impl\TaskServiceImpl
     */
    protected function getCourseTaskService()
    {
        return $this->createService('Task:TaskService');
    }
}
