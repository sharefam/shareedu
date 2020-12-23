<?php

namespace CorporateTrainingBundle\Biz\Exporter;

use AppBundle\Common\ArrayToolkit;

class TeacherOfflineCourseProfileExporter extends AbstractExporter
{
    public function canExport($parameters)
    {
        return true;
    }

    public function getExportFileName()
    {
        return ' teacher_offline_course_profile.xls';
    }

    public function getSortedHeadingRow($parameters)
    {
        $row = array();
        if (!empty($parameters['choices'])) {
            $choices = $parameters['choices'];

            if (in_array('课程名', $choices)) {
                $row[] = array('code' => 'title', 'title' => $this->trans('offline_course.course_title'));
            }
            if (in_array('学员数', $choices)) {
                $row[] = array('code' => 'studentNum', 'title' => $this->trans('teaching.project_plan.course.student_num'));
            }
            if (in_array('出勤率', $choices)) {
                $row[] = array('code' => 'attend', 'title' => $this->trans('offline_activity.statistics.attendance_rate'));
            }
            if (in_array('作业通过率', $choices)) {
                $row[] = array('code' => 'passHomework', 'title' => $this->trans('teaching_record.course.passing_rate'));
            }
            if (in_array('计划时间', $choices)) {
                $row[] = array('code' => 'createdTime', 'title' => $this->trans('offline_course.tasks.task_date'));
            }
            if (in_array('教学评价', $choices)) {
                $row[] = array('code' => 'courseSurveyScore', 'title' => $this->trans('teaching_record.course.survey'));
            }
        }

        return $row;
    }

    public function buildExportData($parameters)
    {
        $memberData = array();
        $conditions = json_decode($parameters['conditions'], true);
        $user = $this->getUserService()->getUser($conditions['userId']);
        unset($conditions['userId']);
        if (!empty($parameters['choices'])) {
            $offlineCourses = $this->getOfflineCourseService()->searchOfflineCourses(
                $conditions,
                array('createdTime' => 'DESC'),
                0,
                PHP_INT_MAX
            );

            foreach ($offlineCourses as &$offlineCourse) {
                if ($parameters['surveyPlugin']) {
                    $offlineCourse['surveyScore'] = $this->getSurveyResultService()->getOfflineCourseSurveyScoreByCourseIdAndProjectPlanId($offlineCourse['id'], $offlineCourse['projectPlanId']);
                }
                $offlineCourse = $this->buildOfflineCourseStatistic($offlineCourse);

                $offlineCourse['startTimeCustom'] = empty($offlineCourse['startTime']) ? '--' : date('Y-m-d', $offlineCourse['startTime']);
                $offlineCourse['endTimeCustom'] = empty($offlineCourse['endTime']) ? '--' : date('Y-m-d', $offlineCourse['endTime']);

                $memberData[] = array(
                    'title' => $offlineCourse['title'],
                    'studentNum' => empty($offlineCourse['memberCount']) ? 0 : $offlineCourse['memberCount'],
                    'attend' => empty($offlineCourse['attendTaskCount']) ? 0 : floor($offlineCourse['attendCount'] * 100 / $offlineCourse['attendTaskCount']),
                    'passHomework' => empty($offlineCourse['hasHomeTaskCount']) ? 0 : floor($offlineCourse['passHomeworkCount'] * 100 / $offlineCourse['hasHomeTaskCount']),
                    'createdTime' => $this->isSameDay($offlineCourse['startTime'], $offlineCourse['endTime']) ? $offlineCourse['startTimeCustom'] : $offlineCourse['startTimeCustom'].'/'.$offlineCourse['endTimeCustom'],
                    'courseSurveyScore' => empty($offlineCourse['surveyScore']) ? 0 : $offlineCourse['surveyScore'],
                );
            }
        }

        $exportData[] = array(
            'sheetName' => $user['nickname'].$this->trans('admin.teacher.list.exporter.offline_course_num'),
            'data' => $memberData,
        );

        return $exportData;
    }

    protected function isSameDay($day, $otherDay)
    {
        $day = date('Y-m-d', $day);
        $otherDay = date('Y-m-d', $otherDay);
        $day = getdate(strtotime($day));
        $otherDay = getdate(strtotime($otherDay));

        if (($day['year'] === $otherDay['year']) && ($day['yday'] === $otherDay['yday'])) {
            return true;
        } else {
            return false;
        }
    }

    protected function buildOfflineCourseStatistic($course)
    {
        $titleColNum = $this->titleColNum;
        $conditions = array('type' => 'offlineCourse', 'offlineCourseId' => $course['id']);

        $offlineCourseTasks = $this->getOfflineCourseTaskService()->searchTasks(
            $conditions,
            array(),
            0,
            PHP_INT_MAX
        );
        $taskIds = ArrayToolkit::column($offlineCourseTasks, 'id');
        $taskIds = empty($taskIds) ? array(-1) : $taskIds;
        $members = $this->getProjectPlanMemberService()->searchProjectPlanMembers(
            array('projectPlanId' => $course['projectPlanId']),
            array(),
            0,
            PHP_INT_MAX
        );
        $course['memberCount'] = count($members);
        $userIds = empty($members) ? array(-1) : ArrayToolkit::column($members, 'userId');
        $taskResultConditions = array('userIds' => $userIds, 'offlineCourseId' => $course['id'], 'taskIds' => $taskIds);

        if (isset($titleColNum['passHomework'])) {
            $hasHomeTasks = $this->getOfflineCourseTaskService()->searchTasks(array_merge($conditions, array('hasHomework' => 1)), array(), 0, PHP_INT_MAX);
            $course['hasHomeTaskCount'] = count($hasHomeTasks) * $course['memberCount'];
            $passHomeworkResult = $this->getOfflineCourseTaskService()->searchTaskResults(array_merge($taskResultConditions, array('homeworkStatus' => 'passed')), array(), 0, PHP_INT_MAX);
            $course['passHomeworkCount'] = count($passHomeworkResult);
        }
        if (isset($titleColNum['attend'])) {
            $course['attendTaskCount'] = count($offlineCourseTasks) * $course['memberCount'];
            $attendResult = $this->getOfflineCourseTaskService()->searchTaskResults(array_merge($taskResultConditions, array('attendStatus' => 'attended')), array(), 0, PHP_INT_MAX);
            $course['attendCount'] = count($attendResult);
        }

        return $course;
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineCourse\Service\Impl\OfflineCourseServiceImpl
     */
    protected function getOfflineCourseService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineCourse:OfflineCourseService');
    }

    protected function getSurveyResultService()
    {
        return $this->createService('SurveyPlugin:Survey:SurveyResultService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\Impl\MemberServiceImpl
     */
    protected function getProjectPlanMemberService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:MemberService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineCourse\Service\Impl\TaskServiceImpl
     */
    protected function getOfflineCourseTaskService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineCourse:TaskService');
    }
}
