<?php

namespace CorporateTrainingBundle\Biz\Exporter;

use AppBundle\Common\ArrayToolkit;
use CorporateTrainingBundle\Common\DateToolkit;

class DataStatisticDepartmentExporter extends BaseDataStatisticsExporter
{
    public function getExportFileName()
    {
        return 'data_statistic_department.xls';
    }

    public function getSortedHeadingRow($parameters)
    {
        return array(
            array('code' => 'orgName', 'title' => $this->trans('student.profile.department')),
            array('code' => 'totalUsers', 'title' => $this->trans('my.department.department_learn_data.total_num')),
            array('code' => 'learnUsers', 'title' => $this->trans('my.department.course_learn_data.learn_person_num')),
            array('code' => 'finishedTaskNum', 'title' => $this->trans('my.department.course_learn_data.finish_course_task_num')),
            array('code' => 'finishedNum', 'title' => $this->trans('my.department.course_learn_data.finish_course_num')),
            array('code' => 'totalLearnTime', 'title' => $this->trans('my.department.department_learn_data.total_learn_time')),
            array('code' => 'avgLearnTime', 'title' => $this->trans('my.department.department_learn_data.average_learn_time')),
        );
    }

    protected function buildExportData($parameters)
    {
        $orgIds = empty($parameters['orgIds']) ? array(-1) : explode(',', $parameters['orgIds']);
        $orgs = $this->getOrgService()->searchOrgs(
            array('orgIds' => $orgIds),
            array(),
            0,
            PHP_INT_MAX
        );
        $conditions = $this->prepareDepartmentLearnConditions($parameters);
        $records = $this->getUserDailyLearnRecordService()->searchRecords(
            $conditions,
            array('id' => 'DESC'),
            0,
            PHP_INT_MAX
        );

        $recordIds = ArrayToolkit::column($records, 'id');

        $learnRecords = $this->getDataStatisticsService()->statisticsLearnRecordGroupByOrgId(array(
            'orgIds' => empty($orgIds) ? array(-1) : $orgIds,
            'recordIds' => empty($recordIds) ? array(-1) : $recordIds,
        ));
        $learnRecords = ArrayToolkit::index($learnRecords, 'orgId');

        $orgUserNums = $this->getUserService()->statisticsOrgUserNumGroupByOrgId();
        $orgUserNums = ArrayToolkit::index($orgUserNums, 'orgId');

        $departmentData = array();

        foreach ($orgs as $org) {
            $totalUsers = empty($orgUserNums[$org['id']]['count']) ? 0 : $orgUserNums[$org['id']]['count'];
            $totalLearnTime = empty($learnRecords[$org['id']]) ? 0 : $learnRecords[$org['id']]['totalLearnTime'];
            $departmentData[] = array(
                'orgName' => empty($org['name']) ? '-' : $org['name'],
                'totalUsers' => $totalUsers,
                'finishedTaskNum' => empty($learnRecords[$org['id']]['totalFinishedTaskNum']) ? '-' : $learnRecords[$org['id']]['totalFinishedTaskNum'],
                'finishedNum' => empty($learnRecords[$org['id']]['finishedCourseNum']) ? '-' : $learnRecords[$org['id']]['finishedCourseNum'],
                'learnUsers' => empty($learnRecords[$org['id']]['learnUserNum']) ? '-' : $learnRecords[$org['id']]['learnUserNum'],
                'totalLearnTime' => DateToolkit::timeToHour($totalLearnTime),
                'avgLearnTime' => DateToolkit::timeToHour($this->getAvgLearnTime($totalUsers, $totalLearnTime)),
            );
        }

        $exportData[] = array(
            'sheetName' => date('Y-m-d', $conditions['startDateTime']).' - '.date('Y-m-d', $conditions['endDateTime']).$this->trans('my.department.data_exporter.department_summary_report'),
            'data' => $departmentData,
        );

        return $exportData;
    }

    protected function prepareDepartmentLearnConditions($fields)
    {
        $conditions = array();

        if (empty($fields['courseCreatedTime'])) {
            list($startDateTime, $endDateTime) = DateToolkit::generateStartDateAndEndDate('year');
            $conditions['startDateTime'] = strtotime($startDateTime);
            $conditions['endDateTime'] = strtotime($endDateTime.' 23:59:59');
        } else {
            $date = explode('-', $fields['courseCreatedTime']);
            $conditions['startDateTime'] = strtotime($date[0]);
            $conditions['endDateTime'] = strtotime($date[1].' 23:59:59');
        }

        if (!empty($fields['postId'])) {
            $conditions['postId'] = $fields['postId'];
        }

        if (!empty($fields['categoryId'])) {
            $conditions['categoryId'] = $fields['categoryId'];
        }

        return $conditions;
    }
}
