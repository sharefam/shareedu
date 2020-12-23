<?php

namespace CorporateTrainingBundle\Biz\Exporter;

use AppBundle\Common\ArrayToolkit;
use CorporateTrainingBundle\Biz\OfflineActivity\Service\CategoryService;
use CorporateTrainingBundle\Common\DateToolkit;

class DataStatisticCategoryExporter extends BaseDataStatisticsExporter
{
    public function getExportFileName()
    {
        return 'data_statistic_category.xls';
    }

    public function getSortedHeadingRow($parameters)
    {
        return array(
            array('code' => 'category', 'title' => $this->trans('course_set.live.category')),
            array('code' => 'learnUsers', 'title' => $this->trans('my.department.course_learn_data.learn_person_num')),
            array('code' => 'finishedTakNum', 'title' => $this->trans('my.department.course_learn_data.finish_course_task_num')),
            array('code' => 'finishedCourseNum', 'title' => $this->trans('my.department.course_learn_data.finish_course_num')),
            array('code' => 'learnTime', 'title' => $this->trans('my.department.user_learn_data.learn_time')),
        );
    }

    protected function buildExportData($parameters)
    {
        $conditions = $this->prepareSearchConditions($parameters);
        $categoryGroup = $this->getCategoryService()->getGroupByCode('course');
        $conditions['groupId'] = $categoryGroup['id'];

        $categories = $this->getCategoryService()->searchCategories(
            $conditions,
            array(),
            0,
            $this->getCategoryService()->countCategories($conditions)
        );

        $noneCategory = array(
            'id' => 0,
            'code' => 'none',
            'name' => $this->trans('my.department.course_learn_data.none_category'),
        );
        array_unshift($categories, $noneCategory);

        $conditions['categoryIds'] = ArrayToolkit::column($categories, 'id');
        $learnRecords = $this->getDataStatisticsService()->statisticsLearnRecordGroupByCategoryId(
            $conditions
        );
        $learnRecords = ArrayToolkit::index($learnRecords, 'categoryId');

        $categoryData = array();
        foreach ($categories as $category) {
            $totalLearnTime = empty($learnRecords[$category['id']]) ? 0 : $learnRecords[$category['id']]['totalLearnTime'];
            $learnUsers = empty($learnRecords[$category['id']]) ? 0 : $learnRecords[$category['id']]['learnUserNum'];
            $totalFinishedTaskNum = empty($learnRecords[$category['id']]) ? 0 : $learnRecords[$category['id']]['totalFinishedTaskNum'];
            $finishedCourseNum = empty($learnRecords[$category['id']]) ? 0 : $learnRecords[$category['id']]['finishedCourseNum'];
            $categoryData[] = array(
                'category' => empty($category['name']) ? '-' : $category['name'],
                'learnUsers' => $learnUsers,
                'finishedTakNum' => $totalFinishedTaskNum,
                'finishedCourseNum' => $finishedCourseNum,
                'learnTime' => DateToolkit::timeToHour($totalLearnTime),
            );
        }

        $exportData[] = array(
            'sheetName' => date('Y-m-d', $conditions['startDateTime']).' - '.date('Y-m-d', $conditions['endDateTime']).$this->trans('my.department.data_exporter.category_summary_report'),
            'data' => $categoryData,
        );

        return $exportData;
    }

    /**
     /**
     * @return CategoryService
     */
    public function getCategoryService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineActivity:CategoryService');
    }
}
