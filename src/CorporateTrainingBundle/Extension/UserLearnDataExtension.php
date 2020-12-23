<?php

namespace CorporateTrainingBundle\Extension;

class UserLearnDataExtension extends Extension
{
    public function getUserLearnDataModules()
    {
        return array(
            'online_course_learn' => array(
                'displayKey' => 'admin.data_center.learn.online_course',
                'service' => 'CorporateTrainingBundle:DataStatistics:DataStatisticsService',
                'method' => 'getOnlineCourseLearnDataForUserLearnDataExtension',
            ),
            'project_plan' => array(
                'displayKey' => 'project_plan',
                'service' => 'CorporateTrainingBundle:ProjectPlan:MemberService',
                'method' => 'getProjectPlanLearnDataForUserLearnDataExtension',
            ),
            'post_course' => array(
                'displayKey' => 'admin.data_center.learn.post_course',
                'service' => 'CorporateTrainingBundle:DataStatistics:DataStatisticsService',
                'method' => 'getPostCourseLearnDataForUserLearnDataExtension',
            ),
            'post_course_progress' => array(
                'displayKey' => '岗位课程累计完成数',
                'service' => 'CorporateTrainingBundle:DataStatistics:DataStatisticsService',
                'method' => 'getPostCourseLearnProgressForUserLearnDataExtension',
            ),
            'post_course_hours' => array(
                'displayKey' => '岗位课程累计学习时长',
                'service' => 'CorporateTrainingBundle:DataStatistics:DataStatisticsService',
                'method' => 'getPostCourseLearnHourForUserLearnDataExtension',
            ),
            'online_study_hours' => array(
                'displayKey' => 'admin.data_center.online_course.study_hours',
                'service' => 'CorporateTrainingBundle:DataStatistics:DataStatisticsService',
                'method' => 'getOnlineStudyHoursLearnDataForUserLearnDataExtension',
            ),
            'offline_study_hours' => array(
                'displayKey' => 'admin.data_center.offline_course.study_hours',
                'service' => 'CorporateTrainingBundle:OfflineCourse:TaskService',
                'method' => 'getOfflineStudyHoursLearnDataForUserLearnDataExtension',
            ),
            'offline_activity' => array(
                'displayKey' => 'admin.data_center.offline_activity',
                'service' => 'CorporateTrainingBundle:OfflineActivity:MemberService',
                'method' => 'getOfflineActivityLearnDataForUserLearnDataExtension',
            ),
            'group_post_num' => array(
                'displayKey' => 'admin.data_center.thread.groupPostNum',
                'service' => 'Group:ThreadService',
                'method' => 'getGroupPostLearnDataForUserLearnDataExtension',
            ),
        );
    }
}
