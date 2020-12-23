<?php

namespace CorporateTrainingBundle\Biz\Exporter;

use AppBundle\Common\ArrayToolkit;
use CorporateTrainingBundle\Common\DateToolkit;

class DataStatisticPostExporter extends BaseDataStatisticsExporter
{
    public function getExportFileName()
    {
        return 'data_statistic_post.xls';
    }

    public function getSortedHeadingRow($parameters)
    {
        return array(
            array('code' => 'postName', 'title' => $this->trans('student.profile.department')),
            array('code' => 'totalUsers', 'title' => $this->trans('my.department.department_learn_data.total_num')),
            array('code' => 'learnUsers', 'title' => $this->trans('my.department.course_learn_data.learn_person_num')),
            array('code' => 'finishedTaskNum', 'title' => $this->trans('my.department.course_learn_data.finish_course_task_num')),
            array('code' => 'finishedNum', 'title' => $this->trans('my.department.course_learn_data.finish_course_num')),
            array('code' => 'totalLearnTime', 'title' => $this->trans('my.department.user_learn_data.total_learn_time')),
            array('code' => 'avgLearnTime', 'title' => $this->trans('my.department.department_learn_data.average_learn_time')),
        );
    }

    protected function buildExportData($parameters)
    {
        $conditions = $this->prepareSearchConditions($parameters);
        $posts = $this->getPostService()->searchPosts(
            $conditions,
            array(),
            0,
            $this->getPostService()->countPosts($conditions)
        );

        $nonePost = array(
            'id' => 0,
            'code' => 'none',
            'name' => $this->trans('my.department.course_learn_data.none_post'),
        );
        array_unshift($posts, $nonePost);

        $conditions['postIds'] = ArrayToolkit::column($posts, 'id');
        $learnRecords = $this->getDataStatisticsService()->statisticsLearnRecordGroupByPostId(
            $conditions
        );
        $learnRecords = ArrayToolkit::index($learnRecords, 'postId');

        $postUserNums = $this->getUserService()->statisticsPostUserNumGroupByPostId();
        $postUserNums = ArrayToolkit::index($postUserNums, 'postId');

        $postData = array();

        foreach ($posts as $post) {
            $totalUsers = empty($postUserNums[$post['id']]['count']) ? 0 : $postUserNums[$post['id']]['count'];
            $totalLearnTime = empty($learnRecords[$post['id']]) ? 0 : $learnRecords[$post['id']]['totalLearnTime'];
            $postData[] = array(
                'postName' => empty($post['name']) ? '-' : $post['name'],
                'totalUsers' => $totalUsers,
                'learnUsers' => empty($learnRecords[$post['id']]['learnUserNum']) ? 0 : $learnRecords[$post['id']]['learnUserNum'],
                'finishedTaskNum' => empty($learnRecords[$post['id']]['totalFinishedTaskNum']) ? 0 : $learnRecords[$post['id']]['totalFinishedTaskNum'],
                'finishedNum' => empty($learnRecords[$post['id']]['finishedCourseNum']) ? 0 : $learnRecords[$post['id']]['finishedCourseNum'],
                'totalLearnTime' => DateToolkit::timeToHour($totalLearnTime),
                'avgLearnTime' => DateToolkit::timeToHour($this->getAvgLearnTime($totalUsers, $totalLearnTime)),
            );
        }

        $exportData[] = array(
            'sheetName' => date('Y-m-d', $conditions['startDateTime']).' - '.date('Y-m-d', $conditions['endDateTime']).$this->trans('my.department.data_exporter.post_summary_report'),
            'data' => $postData,
        );

        return $exportData;
    }
}
