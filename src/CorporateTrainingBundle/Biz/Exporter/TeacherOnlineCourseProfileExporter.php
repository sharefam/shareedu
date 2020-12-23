<?php

namespace CorporateTrainingBundle\Biz\Exporter;

use AppBundle\Common\ArrayToolkit;

class TeacherOnlineCourseProfileExporter extends AbstractExporter
{
    public function canExport($parameters)
    {
        return true;
    }

    public function getExportFileName()
    {
        return ' teacher_online_course_profile.xls';
    }

    public function getSortedHeadingRow($parameters)
    {
        $row = array();
        if (!empty($parameters['choices'])) {
            $choices = $parameters['choices'];

            if (in_array('课程名', $choices)) {
                $row[] = array('code' => 'title', 'title' => $this->trans('admin.teacher.teacher_archives.exporter.field.title'));
            }
            if (in_array('课程分类', $choices)) {
                $row[] = array('code' => 'category', 'title' => $this->trans('admin.teacher.teacher_archives.exporter.field.category'));
            }
            if (in_array('完成人数', $choices)) {
                $row[] = array('code' => 'finishNum', 'title' => $this->trans('admin.teacher.teacher_archives.exporter.field.finish_num'));
            }
            if (in_array('总人数', $choices)) {
                $row[] = array('code' => 'studentNum', 'title' => $this->trans('admin.teacher.teacher_archives.exporter.field.student_num'));
            }
            if (in_array('创建时间', $choices)) {
                $row[] = array('code' => 'createdTime', 'title' => $this->trans('admin.teacher.teacher_archives.exporter.field.create_time'));
            }
            if (in_array('教学评价', $choices)) {
                $row[] = array('code' => 'courseSurveyScore', 'title' => $this->trans('admin.teacher.teacher_archives.exporter.field.survey'));
            }
            if (in_array('人均学习时长', $choices)) {
                $row[] = array('code' => 'averageLearnTime', 'title' => $this->trans('admin.teacher.teacher_archives.exporter.field.average_learn_time'));
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
            $titleColNum = $this->titleColNum;
            $courseSets = $this->getCourseSetService()->searchUserTeachingCourseSets(
                $user['id'],
                $conditions,
                0,
                PHP_INT_MAX
            );

            $categories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($courseSets, 'categoryId'));

            foreach ($courseSets as $courseSet) {
                if ($parameters['surveyPlugin']) {
                    $courseSet['surveyScore'] = $this->getSurveyResultService()->getOnlineCourseSurveyScoreByCourseId($courseSet['defaultCourseId']);
                }
                if (isset($titleColNum['finishNum'])) {
                    $courseSet['learnedStudentNum'] = $this->getCourseMemberService()->countLearnedStudentsByCourseId($courseSet['defaultCourseId']);
                }
                if (isset($titleColNum['averageLearnTime'])) {
                    $courseSet['learnTime'] = $this->getTaskResultService()->sumLearnTimeByCourseId($courseSet['defaultCourseId']);
                    $courseSet['averageLearnTime'] = $courseSet['studentNum'] > 0 ? sprintf('%.2f', $courseSet['learnTime'] / $courseSet['studentNum']) : 0;
                }
                $memberData[] = array(
                    'title' => $courseSet['title'],
                    'category' => empty($categories[$courseSet['categoryId']]) ? '--' : $categories[$courseSet['categoryId']]['name'],
                    'finishNum' => isset($titleColNum['finishNum']) ? $courseSet['learnedStudentNum'] : 0,
                    'studentNum' => $courseSet['studentNum'],
                    'createdTime' => date('Y-m-d', $courseSet['createdTime']),
                    'courseSurveyScore' => empty($courseSet['surveyScore']) ? 0 : $courseSet['surveyScore'],
                    'averageLearnTime' => isset($titleColNum['averageLearnTime']) ? substr(sprintf('%.2f', $courseSet['averageLearnTime'] / 3600), 0, -1) : 0,
                );
            }
        }
        $exportData[] = array(
            'sheetName' => $user['nickname'].$this->trans('admin.teacher.list.exporter.course_num'),
            'data' => $memberData,
        );

        return $exportData;
    }

    /**
     * @return \CorporateTrainingBundle\Biz\Course\Service\Impl\MemberServiceImpl
     */
    protected function getCourseMemberService()
    {
        return $this->createService('CorporateTrainingBundle:Course:MemberService');
    }

    protected function getSurveyResultService()
    {
        return $this->createService('SurveyPlugin:Survey:SurveyResultService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\Task\Service\Impl\TaskResultServiceImpl
     */
    protected function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }

    protected function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }
}
