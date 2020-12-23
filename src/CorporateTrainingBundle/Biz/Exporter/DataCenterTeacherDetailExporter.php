<?php

namespace CorporateTrainingBundle\Biz\Exporter;

use AppBundle\Common\ArrayToolkit;
use CorporateTrainingBundle\Common\OrgToolkit;

class DataCenterTeacherDetailExporter extends AbstractExporter
{
    public function canExport($parameters)
    {
        return true;
    }

    public function getExportFileName()
    {
        return 'data_teacher_detail.xls';
    }

    public function getSortedHeadingRow($parameters)
    {
        $row = array(
            array('code' => 'truename', 'title' => $this->trans('student.profile.truename')),
            array('code' => 'nickname', 'title' => $this->trans('admin.teacher.list.exporter.nickname')),
            array('code' => 'org', 'title' => $this->trans('admin.teacher.list.exporter.org_name')),
            array('code' => 'post', 'title' => $this->trans('student.profile.post')),
            array('code' => 'teacherProfessionField', 'title' => $this->trans('admin.teacher.list.exporter.field')),
            array('code' => 'level', 'title' => $this->trans('admin.teacher.list.exporter.level')),
            array('code' => 'onlineCourseNum', 'title' => $this->trans('admin.data_center.teacher.detail.online_course_num')),
            array('code' => 'onlineCourseStudentNum', 'title' => $this->trans('admin.data_center.teacher.detail.online_course_student_num')),
            array('code' => 'offlineCourseTime', 'title' => $this->trans('admin.data_center.teacher.detail.offline_course_time')),
            array('code' => 'offlineCourseStudentNum', 'title' => $this->trans('admin.data_center.teacher.detail.offline_course_student_num')),
         );

        if ($this->isPluginInstalled('Survey')) {
            $row[] = array('code' => 'onlineCourseSurvey', 'title' => $this->trans('admin.data_center.teacher.detail.online_course_survey'));
            $row[] = array('code' => 'offlineCourseSurvey', 'title' => $this->trans('admin.data_center.teacher.detail.offline_course_survey'));
            $row[] = array('code' => 'teacherSurvey', 'title' => $this->trans('admin.data_center.teacher.detail.teacher_survey'));
        }

        return $row;
    }

    public function buildExportData($parameters)
    {
        $conditions = $this->buildConditions($parameters);

        $users = $this->getUserService()->searchUsers(
            $conditions,
            array('createdTime' => 'DESC'),
            0,
            PHP_INT_MAX
        );

        $userIds = ArrayToolkit::column($users, 'id');

        $userProfiles = $this->getUserService()->findUserProfilesByIds($userIds);
        $userProfiles = ArrayToolkit::index($userProfiles, 'id');

        $posts = $this->getPostService()->findPostsByIds(ArrayToolkit::column($users, 'postId'));
        $posts = ArrayToolkit::index($posts, 'id');

        $teacherProfiles = $this->getProfileService()->searchProfiles(array('userIds' => $userIds), array(), 0, PHP_INT_MAX);
        $teacherProfiles = ArrayToolkit::index($teacherProfiles, 'userId');

        $teacherLevels = $this->getLevelService()->findAllLevels();
        $teacherLevels = ArrayToolkit::index($teacherLevels, 'id');

        $teacherProfessions = $this->getTeacherProfessionFieldService()->findAllTeacherProfessionFields();
        list($users, $orgs) = $this->buildTeacherProfile($users, $conditions);

        $memberData = array();
        foreach ($users as $user) {
            $teacherProfile = empty($teacherProfiles[$user['id']]) ? array() : $teacherProfiles[$user['id']];
            $data = array(
                'truename' => empty($userProfiles[$user['id']]['truename']) ? '--' : $userProfiles[$user['id']]['truename'],
                'nickname' => $user['nickname'],
                'org' => !empty($user['orgIds']) ? OrgToolkit::buildOrgsNames($user['orgIds'], $orgs) : '',
                'post' => empty($posts[$user['postId']]) ? '-' : $posts[$user['postId']]['name'],
                'teacherProfessionField' => empty($teacherProfile['teacherProfessionFieldIds']) ? '--' : OrgToolkit::buildOrgsNames($teacherProfile['teacherProfessionFieldIds'], $teacherProfessions),
                'level' => empty($teacherProfile['levelId']) ? '--' : $teacherLevels[$teacherProfile['levelId']]['name'],
                'onlineCourseNum' => empty($user['courseNum']) ? 0 : $user['courseNum'],
                'onlineCourseStudentNum' => empty($user['courseStudentNum']) ? 0 : $user['courseStudentNum'],
                'offlineCourseTime' => empty($user['offlineCourseTime']) ? 0 : $user['offlineCourseTime'],
                'offlineCourseStudentNum' => empty($user['offlineCourseStudentNum']) ? 0 : $user['offlineCourseStudentNum'],
            );

            if ($this->isPluginInstalled('Survey')) {
                $data['onlineCourseSurvey'] = (empty($user['courseSurveyScore']) ? 0 : $user['courseSurveyScore']).'/5.00';
                $data['offlineCourseSurvey'] = (empty($user['offlineCourseSurveyScore']) ? 0 : $user['offlineCourseSurveyScore']).'/5.00';
                $data['teacherSurvey'] = (empty($user['teacherTotalScore']) ? 0 : $user['teacherTotalScore']).'/5.00';
            }
            $memberData[] = $data;
        }
        $exportData[] = array(
            'data' => $memberData,
        );

        return $exportData;
    }

    protected function buildConditions($conditions)
    {
        $conditions['likeOrgCode'] = $conditions['orgCode'];
        unset($conditions['orgCode']);
        $conditions['roles'] = 'ROLE_TEACHER';
        $conditions['locked'] = 0;
        $courseTime = array('startTime' => strtotime(date('Y').'/01/01 00:00'), 'endTime' => time());

        if (!empty($conditions['courseCreateTime'])) {
            $courseCreateTime = explode('-', $conditions['courseCreateTime']);
            $courseTime['startTime'] = strtotime($courseCreateTime[0]);
            $courseTime['endTime'] = strtotime($courseCreateTime[1].' 23:59:59');
        }

        $conditions['courseCreateTime'] = $courseTime;

        if (isset($conditions['keyword'])) {
            $conditions['keyword'] = trim($conditions['keyword']);
        }
        if (isset($conditions['keywordType']) && !empty($conditions['keyword'])) {
            $conditions[$conditions['keywordType']] = $conditions['keyword'];
            unset($conditions['keywordType']);
            unset($conditions['keyword']);
        }

        return $conditions;
    }

    protected function buildTeacherProfile($users, $conditions)
    {
        $orgIds = array();
        foreach ($users as &$user) {
            $courseSets = $this->getCourseSetService()->searchUserTeachingCourseSets(
                $user['id'],
                $conditions['courseCreateTime'],
                0,
                PHP_INT_MAX
            );
            if (!empty($courseSets)) {
                $user['courseNum'] = count($courseSets);
                $courseSetIds = ArrayToolkit::column($courseSets, 'id');
                $user['courseStudentNum'] = $this->getCourseSetService()->sumStudentNumByCourseSetIds($courseSetIds);
            }

            $offlineCourses = $this->getOfflineCourseService()->findPublishedOfflineCoursesByTeacherIdAndTimeRange($user['id'], $conditions['courseCreateTime']);
            if (!empty($offlineCourses)) {
                $courseIds = ArrayToolkit::column($offlineCourses, 'id');
                $time = $this->getOfflineCourseTaskService()->statisticsOfflineCourseTimeByTimeRangeAndCourseIds($conditions['courseCreateTime'], $courseIds);
                $user['offlineCourseTime'] = round(($time / 3600), 1);
                $projectPlanIds = empty($offlineCourses) ? array(-1) : ArrayToolkit::column($offlineCourses, 'projectPlanId');
                $user['offlineCourseStudentNum'] = $this->getProjectPlanMemberService()->countProjectPlanMembers(array('projectPlanIds' => $projectPlanIds));
            }

            if ($this->isPluginInstalled('Survey')) {
                $courseIds = ArrayToolkit::column($courseSets, 'defaultCourseId');
                $user['courseSurveyScore'] = $this->getSurveyResultService()->getOnlineCoursesAverageSurveyScore($courseIds);
                $user['offlineCourseSurveyScore'] = $this->getSurveyResultService()->getOfflineCoursesAverageSurveyScore(ArrayToolkit::column($offlineCourses, 'id'));
                $user['teacherTotalScore'] = $this->getSurveyResultService()->getTeacherOnlineAndOfflineCoursesAverageSurveyScore($user['id']);
            }
            $orgIds = array_merge($orgIds, $user['orgIds']);
        }

        $orgIds = array_values(array_unique($orgIds));
        $orgs = $this->getOrgService()->findOrgsByIds($orgIds);
        $orgs = ArrayToolkit::index($orgs, 'id');

        return array($users, $orgs);
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

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineCourse\Service\Impl\TaskServiceImpl
     */
    protected function getOfflineCourseTaskService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineCourse:TaskService');
    }
}
