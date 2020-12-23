<?php

namespace CorporateTrainingBundle\Biz\Exporter;

use AppBundle\Common\ArrayToolkit;
use CorporateTrainingBundle\Biz\Course\Service\MemberService;
use CorporateTrainingBundle\Biz\Task\Service\TaskService;
use CorporateTrainingBundle\Biz\Taxonomy\Service\CategoryService;

class CourseStatisticDataExporter extends AbstractExporter
{
    public function canExport($parameters)
    {
        $user = $this->biz['user'];

        return $user->hasPermission('admin_course_set_data');
    }

    public function getExportFileName()
    {
        return 'course_statistic_data.xls';
    }

    protected function getSortedHeadingRow($parameters)
    {
        return array(
            array('code' => 'courseName', 'title' => $this->trans('course_statistic_data.export_field.course_name')),
            array('code' => 'courseCategory', 'title' => $this->trans('course_statistic_data.export_field.course_category')),
            array('code' => 'creator', 'title' => $this->trans('course_statistic_data.export_field.creator')),
            array('code' => 'createdTime', 'title' => $this->trans('course_statistic_data.export_field.created_time')),
            array('code' => 'teachers', 'title' => $this->trans('course_statistic_data.export_field.teachers')),
            array('code' => 'studentNum', 'title' => $this->trans('course_statistic_data.export_field.student_number')),
            array('code' => 'finishedNum', 'title' => $this->trans('course_statistic_data.export_field.finished_number')),
            array('code' => 'learnedTime', 'title' => $this->trans('course_statistic_data.export_field.study_time_minute')),
        );
    }

    protected function buildExportData($parameters)
    {
        $parameters['orgIds'] = $this->prepareOrgIds($parameters);

        $courseSets = $this->getCourseSetService()->searchCourseSets($parameters, array('id' => 'DESC'), 0, PHP_INT_MAX);
        $courseCategories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($courseSets, 'categoryId'));
        $courseIds = ArrayToolkit::column($courseSets, 'defaultCourseId');
        $creatorIds = ArrayToolkit::column($courseSets, 'creator');
        $teachers = $this->getCourseService()->findTeachersByCourseIds($courseIds);
        $teacherIds = ArrayToolkit::column($teachers, 'userId');
        $userProfiles = $this->getUserService()->findUserProfilesByIds(array_merge($teacherIds, $creatorIds));
        $teachers = ArrayToolkit::groupIndex($teachers, 'courseId', 'userId');
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);

        $resultData = array();
        foreach ($courseSets as &$courseSet) {
            $courseCategory = empty($courseCategories[$courseSet['categoryId']]) ? array() : $courseCategories[$courseSet['categoryId']];
            $creatorProfile = empty($userProfiles[$courseSet['creator']]) ? array() : $userProfiles[$courseSet['creator']];
            $defaultCourse = empty($courses[$courseSet['defaultCourseId']]) ? array() : $courses[$courseSet['defaultCourseId']];
            $courseTeachers = empty($defaultCourse) || empty($teachers[$defaultCourse['id']]) ? array() : ArrayToolkit::filter($userProfiles, $teachers[$defaultCourse['id']]);
            $teacherNames = ArrayToolkit::column($courseTeachers, 'truename');
            $learnedTime = $this->getTaskService()->sumCourseSetLearnedTimeByCourseSetId($courseSet['id']);

            $resultData[] = array(
                'courseName' => $courseSet['title'],
                'courseCategory' => empty($courseCategory) ? '' : $courseCategory['name'],
                'creator' => empty($creatorProfile) ? '' : $creatorProfile['truename'],
                'createdTime' => date('Y-m-d H:i', $courseSet['createdTime']),
                'teachers' => empty($teacherNames) ? '' : implode(',', $teacherNames),
                'studentNum' => $courseSet['studentNum'],
                'finishedNum' => empty($defaultCourse) ? 0 : $this->getMemberService()->countMembers(
                    array(
                        'finishedTime_GT' => 0,
                        'courseId' => $defaultCourse['id'],
                        'learnedCompulsoryTaskNumGreaterThan' => $defaultCourse['compulsoryTaskNum'],
                    )
                ),
                'learnedTime' => round($learnedTime / 60),
            );
        }

        $exportData[] = array(
            'data' => $resultData,
        );

        return $exportData;
    }

    protected function prepareOrgIds($conditions)
    {
        $orgIds = $this->biz['user']->getManageOrgIdsRecursively();
        if (!isset($conditions['orgIds'])) {
            return empty($orgIds) ? array(-1) : $orgIds;
        }

        $conditions['orgIds'] = explode(',', $conditions['orgIds']);
        $conditions['orgIds'] = array_intersect($orgIds, $conditions['orgIds']);

        return empty($conditions['orgIds']) ? array(-1) : array_values($conditions['orgIds']);
    }

    /**
     * @return CategoryService
     */
    protected function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }

    /**
     * @return MemberService
     */
    protected function getMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }
}
