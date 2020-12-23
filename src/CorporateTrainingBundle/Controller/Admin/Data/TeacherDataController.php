<?php

namespace CorporateTrainingBundle\Controller\Admin\Data;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\BaseController;
use CorporateTrainingBundle\Common\DateToolkit;
use CorporateTrainingBundle\Common\OrgToolkit;
use Symfony\Component\HttpFoundation\Request;

class TeacherDataController extends BaseController
{
    public function teacherOverviewAction(Request $request)
    {
        return $this->render(
            'CorporateTrainingBundle::admin/data/teacher/teacher-overview.html.twig',
            array(
            )
        );
    }

    public function teacherSurveyDataAction(Request $request)
    {
        $fields = $request->query->all();

        $conditions['roles'] = 'ROLE_TEACHER';
        $conditions['locked'] = 0;
        $users = $this->getUserService()->searchUsers(
            $conditions,
            array('createdTime' => 'DESC'),
            0,
            PHP_INT_MAX
        );
        if (!empty($fields['year']) && 'lastYear' == $fields['year']) {
            $conditions['courseCreateTime'] = array('startTime' => strtotime((date('Y') - 1).'/01/01 00:00'), 'endTime' => strtotime(date('Y').'/01/01 00:00'));
        } else {
            $conditions['courseCreateTime'] = array('startTime' => strtotime(date('Y').'/01/01 00:00'), 'endTime' => time());
        }
        $userIds = ArrayToolkit::column($users, 'id');
        $data = $this->buildTeacherSurveyData($userIds, $conditions, $fields['type']);
        $names = array($this->trans('admin.data_center.chart.x_data'), '1~2', '2~3', '3~4', '4~5');
        foreach ($names as $key => $name) {
            $pieData[] = array('name' => $name, 'value' => $data[$key]);
        }
        $chartData = array(
                'names' => $names,
                'data' => $data,
                'count' => count($users),
                'pieData' => empty($pieData) ? array() : $pieData,
        );

        return $this->createJsonResponse($chartData);
    }

    public function teacherProfessionFieldDataAction(Request $request)
    {
        $data = array();
        $users = $this->getUserService()->searchUsers(
            array('roles' => 'ROLE_TEACHER', 'locked' => 0),
            array('createdTime' => 'DESC'),
            0,
            PHP_INT_MAX
        );
        $userIds = empty($users) ? array(-1) : ArrayToolkit::column($users, 'id');
        $professionFields = $this->getTeacherProfessionFieldService()->findAllTeacherProfessionFields();
        foreach ($professionFields as $professionField) {
            $userProfiles = $this->getProfileService()->searchProfiles(array('userIds' => $userIds, 'likeTeacherProfessionFieldIds' => '|'.$professionField['id'].'|'), array(), 0, PHP_INT_MAX);
            $data['name'][] = $professionField['name'];
            $data['count'][] = count($userProfiles);
            $pieData[] = array('name' => $professionField['name'], 'value' => count($userProfiles));
        }

        $chartData = array(
            'professionFieldNames' => empty($data['name']) ? array() : $data['name'],
            'data' => empty($data['count']) ? array() : $data['count'],
            'pieData' => empty($pieData) ? array() : $pieData,
        );

        return $this->createJsonResponse($chartData);
    }

    public function teacherLevelDataAction(Request $request)
    {
        $data = array();
        $levels = $this->getLevelService()->findAllLevels();
        $users = $this->getUserService()->searchUsers(
            array('roles' => 'ROLE_TEACHER', 'locked' => 0),
            array('createdTime' => 'DESC'),
            0,
            PHP_INT_MAX
        );
        $userIds = empty($users) ? array(-1) : ArrayToolkit::column($users, 'id');
        foreach ($levels as $level) {
            $userProfiles = $this->getProfileService()->searchProfiles(array('userIds' => $userIds, 'levelId' => $level['id']), array(), 0, PHP_INT_MAX);
            $data['name'][] = $level['name'];
            $data['count'][] = count($userProfiles);
            $seriesData[] = array('name' => $level['name'], 'value' => count($userProfiles));
        }

        $chartData = array(
            'levelNames' => empty($data['name']) ? array() : $data['name'],
            'data' => empty($data['count']) ? array() : $data['count'],
            'pieData' => empty($seriesData) ? array() : $seriesData,
        );

        return $this->createJsonResponse($chartData);
    }

    public function teacherDetailAction(Request $request)
    {
        $conditions = $request->query->all();

        $conditions = $this->buildConditions($conditions);
        $paginator = new Paginator(
            $this->get('request'),
            $this->getUserService()->countUsers($conditions),
            10
        );
        $paginator->setBaseUrl($this->generateUrl('admin_data_center_teacher_detail_ajax'));

        $users = $this->getUserService()->searchUsers(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->buildTeacherProfile($users, $conditions);

        return $this->render('admin/data/teacher/teacher-detail.html.twig', array(
            'courseCreateTime' => $conditions['courseCreateTime'],
            'paginator' => $paginator,
            'data' => json_encode($users),
        ));
    }

    public function ajaxTeacherDetailAction(Request $request)
    {
        $conditions = $request->request->all();

        $conditions = $this->buildConditions($conditions);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getUserService()->countUsers($conditions),
            10
        );

        $users = $this->getUserService()->searchUsers(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $users = $this->buildTeacherProfile($users, $conditions);

        return $this->render('admin/data/teacher/detail-tr.html.twig', array(
            'paginator' => $paginator,
            'data' => json_encode($users),
        ));
    }

    protected function buildConditions($conditions)
    {
        $conditions = $this->fillOrgCode($conditions);
        $conditions['roles'] = 'ROLE_TEACHER';
        $conditions['locked'] = 0;
        list($startDateTime, $endDateTime) = DateToolkit::generateStartDateAndEndDate('year');

        $courseTime = array('startTime' => strtotime($startDateTime), 'endTime' => strtotime($endDateTime));

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
        $userIds = ArrayToolkit::column($users, 'id');
        $teacherProfiles = $this->getProfileService()->searchProfiles(array('userIds' => $userIds), array(), 0, PHP_INT_MAX);
        $teacherProfiles = ArrayToolkit::index($teacherProfiles, 'userId');
        $teacherLevels = $this->getLevelService()->findAllLevels();
        $teacherLevels = ArrayToolkit::index($teacherLevels, 'id');
        $posts = $this->getPostService()->findPostsByIds(ArrayToolkit::column($users, 'postId'));
        $posts = ArrayToolkit::index($posts, 'id');

        $data = array();
        foreach ($users as &$user) {
            $userData = array();
            $courseSets = $this->getCourseSetService()->searchUserTeachingCourseSets(
                $user['id'],
                array_merge(array('excludeStatus' => array('draft')), $conditions['courseCreateTime']),
                0,
                PHP_INT_MAX
            );
            if (!empty($courseSets)) {
                $userData['courseNum'] = count($courseSets);
                $courseSetIds = ArrayToolkit::column($courseSets, 'id');
                $userData['courseStudentNum'] = $this->getCourseSetService()->sumStudentNumByCourseSetIds($courseSetIds);
            }

            $offlineCourses = $this->getOfflineCourseService()->findPublishedOfflineCoursesByTeacherIdAndTimeRange($user['id'], $conditions['courseCreateTime']);
            if (!empty($offlineCourses)) {
                $courseIds = ArrayToolkit::column($offlineCourses, 'id');
                $time = $this->getOfflineCourseTaskService()->statisticsOfflineCourseTimeByTimeRangeAndCourseIds($conditions['courseCreateTime'], $courseIds);
                $userData['offlineCourseTime'] = round(($time / 3600), 1);
                $projectPlanIds = empty($offlineCourses) ? array(-1) : ArrayToolkit::column($offlineCourses, 'projectPlanId');
                $userData['offlineCourseStudentNum'] = $this->getProjectPlanMemberService()->countProjectPlanMembers(array('projectPlanIds' => $projectPlanIds));
            }

            if ($this->isPluginInstalled('Survey')) {
                $courseIds = ArrayToolkit::column($courseSets, 'defaultCourseId');
                $userData['courseSurveyScore'] = $this->getSurveyResultService()->getOnlineCoursesAverageSurveyScore($courseIds);
                $userData['offlineCourseSurveyScore'] = $this->getSurveyResultService()->getOfflineCoursesAverageSurveyScore(ArrayToolkit::column($offlineCourses, 'id'));
                $userData['teacherTotalScore'] = $this->getSurveyResultService()->getTeacherOnlineAndOfflineCoursesAverageSurveyScore($user['id']);
            }

            $data[] = $this->buildTeacherDetailUserData($user, $posts, $teacherProfiles, $teacherLevels, $userData);
        }

        return $data;
    }

    protected function buildTeacherDetailUserData($user, $posts, $teacherProfiles, $teacherLevels, $userData)
    {
        $teacherProfile = empty($teacherProfiles[$user['id']]) ? array() : $teacherProfiles[$user['id']];
        $orgs = $this->getOrgService()->findOrgsByIds($user['orgIds']);
        $orgs = OrgToolkit::buildOrgsNamesAndCodes($user['orgIds'], $orgs);
        $org['name'] = $orgs[0]['name'];
        $org['code'] = $orgs[0]['code'];
        $org['orgs'] = $orgs;
        $teacherProfessionFieldIds = empty($teacherProfiles[$user['id']]) ? array(-1) : $teacherProfiles[$user['id']]['teacherProfessionFieldIds'];
        $teacherProfessionFields = $this->getTeacherProfessionFieldService()->findTeacherProfessionFieldsByIds($teacherProfessionFieldIds);
        $userProfile = $this->getUserService()->getUserProfile($user['id']);
        $userData['truename'] = empty($userProfile['truename']) ? $user['nickname'] : $userProfile['truename'];
        $userData['post'] = empty($posts[$user['postId']]) ? '-' : $posts[$user['postId']]['name'];
        $userData['level'] = empty($teacherProfile['levelId']) ? '--' : $teacherLevels[$teacherProfile['levelId']]['name'];
        $userData['courseNum'] = isset($userData['courseNum']) ? $userData['courseNum'] : 0;
        $userData['courseStudentNum'] = isset($userData['courseStudentNum']) ? $userData['courseStudentNum'] : 0;
        $userData['offlineCourseTime'] = isset($userData['offlineCourseTime']) ? $userData['offlineCourseTime'] : 0;
        $userData['offlineCourseStudentNum'] = isset($userData['offlineCourseStudentNum']) ? $userData['offlineCourseStudentNum'] : 0;
        $userData['courseSurveyScore'] = (empty($userData['courseSurveyScore']) ? '--' : $userData['courseSurveyScore']).'/5.00';
        $userData['offlineCourseSurveyScore'] = (empty($userData['offlineCourseSurveyScore']) ? '--' : $userData['offlineCourseSurveyScore']).'/5.00';
        $userData['teacherTotalScore'] = (empty($userData['teacherTotalScore']) ? '--' : $userData['teacherTotalScore']).'/5.00';
        $userData['archivesPatch'] = $this->generateUrl('admin_teacher_course_archives', array('userId' => $user['id'], 'from' => 'data-center'));
        $userData['userDetailPatch'] = $this->generateUrl('admin_user_show', array('id' => $user['id']));
        $userData['professionField'] = $this->buildProfessionFields($teacherProfessionFieldIds, $teacherProfessionFields);
        $userData['org'] = $org;
        $userData['coverImgUri'] = $this->getCoverImgUri($user, 'middle');

        return $userData;
    }

    protected function getCoverImgUri($user, $type)
    {
        $coverPath = $this->get('web.twig.app_extension')->userAvatar($user, 'medium');

        return $this->getWebExtension()->getFpath($coverPath, 'avatar.png');
    }

    protected function buildProfessionFields(array $professionFieldIds, array $professionFields, $delimiter = '/')
    {
        $teacherProfessionField = reset($professionFields);

        $teacherProfessionFields = array('fieldNames' => '--', 'fieldName' => '--');

        if (empty($professionFieldIds) || empty($professionFields)) {
            return $teacherProfessionFields;
        }

        $teacherProfessionFieldNames = '';
        $professionFields = ArrayToolkit::index($professionFields, 'id');
        foreach ($professionFieldIds as $professionFieldId) {
            if (!empty($professionFields[$professionFieldId])) {
                $teacherProfessionFieldNames = $teacherProfessionFieldNames.$delimiter.$professionFields[$professionFieldId]['name'];
            }
        }

        $teacherProfessionFields['fieldNames'] = trim($teacherProfessionFieldNames, $delimiter);
        $teacherProfessionFields['fieldName'] = empty($teacherProfessionField) ? '' : $teacherProfessionField['name'];

        return  $teacherProfessionFields;
    }

    protected function buildTeacherSurveyData($userIds, $conditions, $type)
    {
        $data = array(0, 0, 0, 0, 0);
        foreach ($userIds as &$id) {
            $surveyScore = 0;
            $surveyResultCount = 0;
            if ('offline' != $type) {
                list($onlineSurveyScore, $onlineSurveyResultCount) = $this->buildTeacherSurveyOnlineCourseData($conditions, $id);
                $surveyScore += $onlineSurveyScore;
                $surveyResultCount += $onlineSurveyResultCount;
            }

            if ('online' != $type) {
                list($offlineSurveyScore, $offlineSurveyResultCount) = $this->buildTeacherSurveyOfflineCourseData($conditions, $id);
                $surveyScore += $offlineSurveyScore;
                $surveyResultCount += $offlineSurveyResultCount;
            }

            $surveyScore = $surveyResultCount > 0 ? sprintf('%.2f', $surveyScore / $surveyResultCount) : 0;

            if ($surveyScore < 1) {
                ++$data[0];
            } elseif ($surveyScore >= 1 && $surveyScore <= 2) {
                ++$data[1];
            } elseif ($surveyScore > 2 && $surveyScore <= 3) {
                ++$data[2];
            } elseif ($surveyScore > 3 && $surveyScore <= 4) {
                ++$data[3];
            } else {
                ++$data[4];
            }
        }

        return $data;
    }

    protected function buildTeacherSurveyOnlineCourseData($conditions, $userId)
    {
        $surveyScore = 0;
        $surveyResultCount = 0;
        $courseSets = $this->getCourseSetService()->searchUserTeachingCourseSets(
            $userId,
            array_merge(array('excludeStatus' => array('draft')), $conditions['courseCreateTime']),
            0,
            PHP_INT_MAX
        );
        if (!empty($courseSets) && $this->isPluginInstalled('Survey')) {
            $courseIds = ArrayToolkit::column($courseSets, 'defaultCourseId');
            $activities = $this->getActivityService()->findActivitiesByCourseIdsAndType($courseIds, 'questionnaire');
            $members = $this->getCourseService()->findStudentsByCourseIds($courseIds);
            $userIds = empty($members) ? array(-1) : ArrayToolkit::column($members, 'userId');
            $surveyIds = empty($activities) ? array(-1) : ArrayToolkit::column($activities, 'mediaId');
            $surveyScore = $this->getSurveyResultService()->sumScoreBySurveyIdsAndUserIds($surveyIds, $userIds);

            $surveyResultCount = $this->getSurveyResultService()->countSurveyResults(array(
                'surveyIds' => $surveyIds,
                'status' => 'finished',
                'userIds' => $userIds,
            ));
        }

        return array($surveyScore, $surveyResultCount);
    }

    protected function buildTeacherSurveyOfflineCourseData($conditions, $userId)
    {
        $surveyScore = 0;
        $surveyResultCount = 0;
        $offlineCourses = $this->getOfflineCourseService()->searchOfflineCourses(array_merge($conditions['courseCreateTime'], array('status' => 'published', 'teacherId' => $userId)), array(), 0, PHP_INT_MAX);
        if (!empty($offlineCourses) && $this->isPluginInstalled('Survey')) {
            $offlineCourseIds = empty($offlineCourses) ? array(-1) : ArrayToolkit::column($offlineCourses, 'id');
            $projectPlanIds = empty($offlineCourses) ? array(-1) : ArrayToolkit::column($offlineCourses, 'projectPlanId');
            $offlineMembers = $this->getProjectPlanMemberService()->searchProjectPlanMembers(array('projectPlanIds' => $projectPlanIds), array(), 0, PHP_INT_MAX);

            $offlineActivities = $this->getActivityService()->findActivitiesByCourseIdsAndType($offlineCourseIds, 'offlineCourseQuestionnaire');
            $userIds = empty($offlineMembers) ? array(-1) : ArrayToolkit::column($offlineMembers, 'userId');
            $surveyIds = empty($offlineActivities) ? array(-1) : ArrayToolkit::column($offlineActivities, 'mediaId');
            $surveyScore = $this->getSurveyResultService()->sumScoreBySurveyIdsAndUserIds($surveyIds, $userIds);

            $surveyResultCount = $this->getSurveyResultService()->countSurveyResults(array(
                'surveyIds' => $surveyIds,
                'status' => 'finished',
                'userIds' => $userIds,
            ));
        }

        return array($surveyScore, $surveyResultCount);
    }

    /**
     * @return \CorporateTrainingBundle\Biz\Course\Service\Impl\CourseSetServiceImpl
     */
    protected function getCourseSetService()
    {
        return $this->createService('CorporateTrainingBundle:Course:CourseSetService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineCourse\Service\Impl\OfflineCourseServiceImpl
     */
    protected function getOfflineCourseService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineCourse:OfflineCourseService');
    }

    /**
     * @return \SurveyPlugin\Biz\Survey\Service\Impl\SurveyResultServiceImpl
     */
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
     * @return \CorporateTrainingBundle\Biz\Teacher\Service\Impl\ProfileServiceImpl
     */
    protected function getProfileService()
    {
        return $this->createService('CorporateTrainingBundle:Teacher:ProfileService');
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
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\Impl\ProjectPlanServiceImpl
     */
    protected function getProjectPlanService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:ProjectPlanService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineCourse\Service\Impl\TaskServiceImpl
     */
    protected function getOfflineCourseTaskService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineCourse:TaskService');
    }

    protected function getPostService()
    {
        return $this->createService('CorporateTrainingBundle:Post:PostService');
    }

    /**
     * @return \Biz\Activity\Service\Impl\ActivityServiceImpl
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    /**
     * @return \Biz\Course\Service\Impl\CourseServiceImpl
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }
}
