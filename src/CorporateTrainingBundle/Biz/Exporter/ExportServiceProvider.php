<?php

namespace CorporateTrainingBundle\Biz\Exporter;

use AppBundle\Extension\Extension;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ExportServiceProvider extends Extension implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        $this->registerExportType($biz);
        $biz['export_factory'] = function ($biz) {
            $exporter = new ExporterFactory();
            $exporter->setBiz($biz);

            return $exporter;
        };
    }

    protected function registerExportType($biz)
    {
        $biz['export.offline_activity_member'] = function ($biz) {
            return new OfflineActivityMemberExporter($biz);
        };

        $biz['export.batch_grade_example'] = function ($biz) {
            return new OfflineActivityBatchGradeExampleExporter($biz);
        };

        $biz['export.data_statistic_department'] = function ($biz) {
            return new DataStatisticDepartmentExporter($biz);
        };

        $biz['export.data_statistic_post'] = function ($biz) {
            return new DataStatisticPostExporter($biz);
        };

        $biz['export.data_statistic_category'] = function ($biz) {
            return new DataStatisticCategoryExporter($biz);
        };

        $biz['export.project_plan_member'] = function ($biz) {
            return new  ProjectPlanMemberExporter($biz);
        };

        $biz['export.project_plan_enrollment_record'] = function ($biz) {
            return new  ProjectPlanEnrollmentRecordExporter($biz);
        };

        $biz['export.project_plan_offline_course_attendance'] = function ($biz) {
            return new ProjectPlanOfflineCourseAttendanceExporter($biz);
        };

        $biz['export.project_plan_offline_course_homework_record'] = function ($biz) {
            return new ProjectPlanOfflineCourseHomeworkRecordExporter($biz);
        };

        $biz['export.project_plan_offline_exam_result'] = function ($biz) {
            return new ProjectPlanOfflineExamResultExporter($biz);
        };

        $biz['export.project_plan_offline_exam_member'] = function ($biz) {
            return new ProjectPlanOfflineExamMemberExporter($biz);
        };

        $biz['export.project_plan_member_statistic_data'] = function ($biz) {
            return new ProjectPlanMemberStatisticDataExporter($biz);
        };

        $biz['export.teacher_profile'] = function ($biz) {
            return new TeacherProfileExporter($biz);
        };

        $biz['export.teacher_online__course_profile'] = function ($biz) {
            return new TeacherOnlineCourseProfileExporter($biz);
        };

        $biz['export.teacher_offline__course_profile'] = function ($biz) {
            return new TeacherOfflineCourseProfileExporter($biz);
        };

        $biz['export.org_list'] = function ($biz) {
            return new OrgListExporter($biz);
        };

        $biz['export.post_list'] = function ($biz) {
            return new PostListExporter($biz);
        };

        $biz['export.data_center_teacher_detail'] = function ($biz) {
            return new DataCenterTeacherDetailExporter($biz);
        };

        $biz['export.data_center_user_detail'] = function ($biz) {
            return new DataCenterUserDetailExporter($biz);
        };

        $biz['export.data_center_project_plan_detail'] = function ($biz) {
            return new DataCenterProjectPlanDetailExporter($biz);
        };

        $biz['export.testpaper_result'] = function ($biz) {
            return new TestpaperResultExporter($biz);
        };

        $biz['export.course_statistic_data'] = function ($biz) {
            return new CourseStatisticDataExporter($biz);
        };
    }
}
