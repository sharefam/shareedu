<?php

namespace CorporateTrainingBundle\Biz\Exporter;

use AppBundle\Common\ArrayToolkit;
use CorporateTrainingBundle\Common\OrgToolkit;

class TeacherProfileExporter extends AbstractExporter
{
    public function canExport($parameters)
    {
        return true;
    }

    public function getExportFileName()
    {
        return ' teacher_profile.xls';
    }

    public function getSortedHeadingRow($parameters)
    {
        $row = array();
        if (!empty($parameters['choices'])) {
            $choices = $parameters['choices'];

            if (in_array('用户名', $choices)) {
                $row[] = array('code' => 'nickname', 'title' => $this->trans('admin.teacher.list.exporter.nickname'));
            }
            if (in_array('是否推荐', $choices)) {
                $row[] = array('code' => 'promote', 'title' => $this->trans('admin.teacher.list.exporter.promote'));
            }
            if (in_array('组织机构', $choices)) {
                $row[] = array('code' => 'org', 'title' => $this->trans('admin.teacher.list.exporter.org_name'));
            }
            if (in_array('专业领域', $choices)) {
                $row[] = array('code' => 'teacherProfessionField', 'title' => $this->trans('admin.teacher.list.exporter.field'));
            }
            if (in_array('等级', $choices)) {
                $row[] = array('code' => 'level', 'title' => $this->trans('admin.teacher.list.exporter.level'));
            }
            if (in_array('在教线上课程', $choices)) {
                $row[] = array('code' => 'courseNum', 'title' => $this->trans('admin.teacher.list.exporter.course_num'));
            }
            if (in_array('线上课程综合教评', $choices)) {
                $row[] = array('code' => 'courseSurveyScore', 'title' => $this->trans('admin.teacher.list.exporter.course_survey_score'));
            }
            if (in_array('线上教学人次', $choices)) {
                $row[] = array('code' => 'courseStudentNum', 'title' => $this->trans('admin.teacher.list.exporter.course_student_num'));
            }
            if (in_array('在教线下课程', $choices)) {
                $row[] = array('code' => 'offlineCourseNum', 'title' => $this->trans('admin.teacher.list.exporter.offline_course_num'));
            }
            if (in_array('线下课程综合教评', $choices)) {
                $row[] = array('code' => 'offlineCourseSurveyScore', 'title' => $this->trans('admin.teacher.list.exporter.offline_course_survey_score'));
            }
            if (in_array('线下教学人次', $choices)) {
                $row[] = array('code' => 'offlineCourseStudentNum', 'title' => $this->trans('admin.teacher.list.exporter.offline_course_student_num'));
            }
        }

        return $row;
    }

    public function buildExportData($parameters)
    {
        $memberData = array();
        if (!empty($parameters['choices'])) {
            $titleColNum = $this->titleColNum;
            $conditions = json_decode($parameters['conditions'], true);
            $users = $this->getUserService()->searchUsers(
                $conditions,
                array('createdTime' => 'DESC'),
                0,
                PHP_INT_MAX
            );
            $teacherProfessions = array();
            $teacherLevels = array();
            $userIds = ArrayToolkit::column($users, 'id');
            $teacherProfiles = $this->getProfileService()->searchProfiles(array('userIds' => $userIds), array(), 0, PHP_INT_MAX);
            $teacherProfiles = ArrayToolkit::index($teacherProfiles, 'userId');
            if (isset($titleColNum['level'])) {
                $teacherLevels = $this->getLevelService()->findAllLevels();
                $teacherLevels = ArrayToolkit::index($teacherLevels, 'id');
            }
            if (isset($titleColNum['teacherProfessionField'])) {
                $teacherProfessions = $this->getTeacherProfessionFieldService()->findAllTeacherProfessionFields();
                $teacherProfessions = ArrayToolkit::index($teacherProfessions, 'id');
            }
            $users = $this->buildTeacherProfile($users, $conditions, $parameters['surveyPlugin']);

            $orgs = array();
            if (isset($titleColNum['org'])) {
                $orgIds = array();
                foreach ($users as $user) {
                    $orgIds = array_merge($orgIds, $user['orgIds']);
                }
                $orgIds = array_values(array_unique($orgIds));
                $orgs = $this->getOrgService()->findOrgsByIds($orgIds);
                $orgs = ArrayToolkit::index($orgs, 'id');
            }

            foreach ($users as $user) {
                $teacherProfile = empty($teacherProfiles[$user['id']]) ? array() : $teacherProfiles[$user['id']];
                $memberData[] = array(
                    'nickname' => $user['nickname'],
                    'promote' => '1' == $user['promoted'] ? $this->trans('site.datagrid.filter.recommended') : $this->trans('site.datagrid.filter.unrecommended'),
                    'org' => isset($titleColNum['org']) ? OrgToolkit::buildOrgsNames($user['orgIds'], $orgs) : '',
                    'teacherProfessionField' => empty($teacherProfile['teacherProfessionFieldIds']) ? '--' : OrgToolkit::buildOrgsNames($teacherProfile['teacherProfessionFieldIds'], $teacherProfessions),
                    'level' => empty($teacherProfile['levelId']) ? '--' : $teacherLevels[$teacherProfile['levelId']]['name'],
                    'courseNum' => empty($user['courseNum']) ? 0 : $user['courseNum'],
                    'courseSurveyScore' => empty($user['courseSurveyScore']) ? 0 : $user['courseSurveyScore'],
                    'courseStudentNum' => empty($user['courseStudentNum']) ? 0 : $user['courseStudentNum'],
                    'offlineCourseNum' => empty($user['offlineCourseNum']) ? 0 : $user['offlineCourseNum'],
                    'offlineCourseSurveyScore' => empty($user['offlineCourseSurveyScore']) ? 0 : $user['offlineCourseSurveyScore'],
                    'offlineCourseStudentNum' => empty($user['offlineCourseStudentNum']) ? 0 : $user['offlineCourseStudentNum'],
                );
            }
        }
        $exportData[] = array(
            'data' => $memberData,
        );

        return $exportData;
    }

    protected function buildTeacherProfile($users, $conditions, $isPluginInstalled)
    {
        $titleColNum = $this->titleColNum;

        foreach ($users as &$user) {
            $courseSets = $this->getCourseSetService()->searchUserTeachingCourseSets(
                $user['id'],
                $conditions['courseCreateTime'],
                0,
                PHP_INT_MAX
            );
            if (!empty($courseSets)) {
                $user['courseNum'] = count($courseSets);
                if (isset($titleColNum['courseStudentNum'])) {
                    $courseSetIds = ArrayToolkit::column($courseSets, 'id');
                    $user['courseStudentNum'] = $this->getCourseSetService()->sumStudentNumByCourseSetIds($courseSetIds);
                }
            }

            $offlineCourses = $this->getOfflineCourseService()->searchOfflineCourses(array_merge($conditions['courseCreateTime'], array('teacherId' => $user['id'])), array(), 0, PHP_INT_MAX);

            if (!empty($offlineCourses)) {
                $user['offlineCourseNum'] = count($offlineCourses);
                if (isset($titleColNum['offlineCourseStudentNum'])) {
                    $user['offlineCourseStudentNum'] = 0;
                    foreach ($offlineCourses as $offlineCourse) {
                        $user['offlineCourseStudentNum'] += $this->getProjectPlanMemberService()->countProjectPlanMembers(array('projectPlanId' => $offlineCourse['projectPlanId']));
                    }
                }
            }

            if ($isPluginInstalled) {
                if (isset($titleColNum['courseSurveyScore'])) {
                    $courseIds = ArrayToolkit::column($courseSets, 'defaultCourseId');
                    $user['courseSurveyScore'] = $this->getSurveyResultService()->getOnlineCoursesAverageSurveyScore($courseIds);
                }
                if (isset($titleColNum['offlineCourseSurveyScore'])) {
                    $user['offlineCourseSurveyScore'] = $this->getSurveyResultService()->getOfflineCoursesAverageSurveyScore(ArrayToolkit::column($offlineCourses, 'id'));
                }
            }
        }

        return $users;
    }

    /**
     * @return \CorporateTrainingBundle\Biz\TeacherProfessionField\Service\Impl\TeacherProfessionFieldServiceImpl
     */
    protected function getTeacherProfessionFieldService()
    {
        return $this->createService('CorporateTrainingBundle:TeacherProfessionField:TeacherProfessionFieldService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\Teacher\Service\Impl\LevelServiceImpl
     */
    protected function getLevelService()
    {
        return $this->createService('CorporateTrainingBundle:Teacher:LevelService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\Teacher\Service\Impl\ProfileServiceImpl
     */
    protected function getProfileService()
    {
        return $this->createService('CorporateTrainingBundle:Teacher:ProfileService');
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
}
