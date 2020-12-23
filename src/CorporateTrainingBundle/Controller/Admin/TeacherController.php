<?php

namespace CorporateTrainingBundle\Controller\Admin;

use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\Paginator;
use AppBundle\Controller\Admin\TeacherController as BaseController;
use Topxia\Service\Common\ServiceKernel;

class TeacherController extends BaseController
{
    public function indexAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $conditions = $request->query->all();
        $conditions = $this->buildConditions($conditions);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getUserService()->countUsers($conditions),
            20
        );

        $users = $this->getUserService()->searchUsers(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userIds = ArrayToolkit::column($users, 'id');
        $teacherProfiles = $this->getProfileService()->searchProfiles(array('userIds' => $userIds), array(), 0, PHP_INT_MAX);
        $teacherProfiles = ArrayToolkit::index($teacherProfiles, 'userId');
        $teacherLevels = $this->getLevelService()->findAllLevels();
        $teacherProfessions = $this->getTeacherProfessionFieldService()->findAllTeacherProfessionFields();
        $users = $this->buildTeacherProfile($users, $conditions);

        return $this->render('admin/teacher/index.html.twig', array(
            'user' => $user,
            'users' => $users,
            'teacherLevels' => $teacherLevels,
            'teacherProfessions' => $teacherProfessions,
            'teacherProfiles' => $teacherProfiles,
            'courseCreateTime' => $conditions['courseCreateTime'],
            'paginator' => $paginator,
        ));
    }

    public function promoteListAction(Request $request)
    {
        $conditions = $request->query->all();
        if (isset($conditions['keyword'])) {
            $conditions['keyword'] = trim($conditions['keyword']);
        }
        if (isset($conditions['keywordType']) && !empty($conditions['keyword'])) {
            $conditions[$conditions['keywordType']] = $conditions['keyword'];
            unset($conditions['keywordType']);
            unset($conditions['keyword']);
        }

        $conditions['roles'] = 'ROLE_TEACHER';
        $conditions['promoted'] = 1;
        $conditions = $this->fillOrgCode($conditions);

        $users = $this->getUserService()->searchUsers(
            $conditions,
            array('promotedSeq' => 'ASC', 'promotedTime' => 'DESC'),
            0,
            PHP_INT_MAX
        );

        return $this->render('admin/teacher/teacher-promote-list.html.twig', array(
            'users' => $users,
        ));
    }

    public function sortAction(Request $request)
    {
        $ids = $request->request->get('ids');
        $this->getUserService()->sortPromoteUser($ids);

        return $this->createJsonResponse(true);
    }

    public function teacherProfessionFieldAction(Request $request)
    {
        $paginator = new Paginator(
            $this->get('request'),
            $this->getTeacherProfessionFieldService()->countTeacherProfessionFields(array()),
            20
        );

        $professionFields = $this->getTeacherProfessionFieldService()->searchTeacherProfessionFields(
            array(),
            array('seq' => 'ASC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('admin/teacher/profession-field-list.html.twig', array(
            'fields' => $professionFields,
            'paginator' => $paginator,
        ));
    }

    public function teacherProfessionFieldCreateAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $field = $request->request->all();
            $this->getTeacherProfessionFieldService()->createTeacherProfessionField($field);

            return $this->redirect($this->generateUrl('admin_teacher_profession_field'));
        }

        return $this->render('admin/teacher/profession-field-modal.html.twig');
    }

    public function teacherProfessionFieldUpdateAction(Request $request, $id)
    {
        $professionField = $this->getTeacherProfessionFieldService()->getTeacherProfessionField($id);

        if ('POST' == $request->getMethod()) {
            $field = $request->request->all();
            $this->getTeacherProfessionFieldService()->updateTeacherProfessionField($id, $field);

            return $this->redirect($this->generateUrl('admin_teacher_profession_field'));
        }

        return $this->render('admin/teacher/profession-field-modal.html.twig', array(
            'field' => $professionField,
        ));
    }

    public function teacherProfessionFieldNameCheckAction(Request $request)
    {
        $name = $request->query->get('value');
        $exclude = $request->query->get('exclude');
        $available = $this->getTeacherProfessionFieldService()->isTeacherProfessionFieldNameAvailable($name, $exclude);

        if ($available) {
            $result = array(
                'success' => true,
                'message' => '',
            );
        } else {
            $result = array(
                'success' => false,
                'message' => ServiceKernel::instance()->trans('admin.teacher.message.field_exist'),
            );
        }

        return $this->createJsonResponse($result);
    }

    public function teacherProfessionFieldDeleteAction(Request $request, $id)
    {
        $userProfiles = $this->getProfileService()->searchProfiles(array('likeTeacherProfessionFieldIds' => '|'.$id.'|'), array(), 0, PHP_INT_MAX);
        if (empty($userProfiles)) {
            $result = $this->getTeacherProfessionFieldService()->deleteTeacherProfessionField($id);

            if ($result) {
                $result = array('success' => $result, 'message' => ServiceKernel::instance()->trans('admin.teacher.message.delete_field_success'));
            } else {
                $result = array('success' => $result, 'message' => ServiceKernel::instance()->trans('admin.teacher.message.delete_field_error'));
            }
        } else {
            $result = array('success' => false, 'message' => ServiceKernel::instance()->trans('admin.teacher.message.delete_field_error.teacher_not_empty'));
        }

        return $this->createJsonResponse($result);
    }

    public function levelListAction(Request $request)
    {
        $paginator = new Paginator(
            $this->get('request'),
            $this->getLevelService()->countLevels(array()),
            20
        );

        $levels = $this->getLevelService()->searchLevels(array(), array(), $paginator->getOffsetCount(), $paginator->getPerPageCount());

        return $this->render('admin/teacher/level/list.html.twig', array(
            'levels' => $levels,
            'paginator' => $paginator,
        ));
    }

    public function createLevelAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $fields = $request->request->all();
            $this->getLevelService()->createLevel($fields);

            return $this->redirect($this->generateUrl('admin_teacher_level'));
        }

        return $this->render('admin/teacher/level/level-modal.html.twig', array());
    }

    public function updateLevelAction(Request $request, $id)
    {
        $level = $this->getLevelService()->getLevel($id);
        if ('POST' == $request->getMethod()) {
            $fields = $request->request->all();
            $this->getLevelService()->updateLevel($id, $fields);

            return $this->redirect($this->generateUrl('admin_teacher_level'));
        }

        return $this->render('admin/teacher/level/level-modal.html.twig', array('level' => $level));
    }

    public function deleteLevelAction(Request $request, $id)
    {
        $userProfiles = $this->getProfileService()->findProfilesByLevelId($id);

        if (empty($userProfiles)) {
            $result = $this->getLevelService()->deleteLevel($id);
            if ($result) {
                $result = array('success' => $result, 'message' => ServiceKernel::instance()->trans('admin.teacher.message.delete_level_success'));
            } else {
                $result = array('success' => $result, 'message' => ServiceKernel::instance()->trans('admin.teacher.message.delete_level_error'));
            }
        } else {
            $result = array('success' => false, 'message' => ServiceKernel::instance()->trans('admin.teacher.message.delete_level_error.teacher_not_empty'));
        }

        return $this->createJsonResponse($result);
    }

    public function checkLevelAction(Request $request)
    {
        $name = $request->query->get('value');
        $exclude = $request->query->get('exclude');

        $available = $this->getLevelService()->isLevelNameAvailable($name, $exclude);

        if ($available) {
            $result = array('success' => true, 'message' => '');
        } else {
            $result = array('success' => false, 'message' => ServiceKernel::instance()->trans('admin.teacher.message.level_exist'));
        }

        return $this->createJsonResponse($result);
    }

    public function levelMatchAction(Request $request)
    {
        $likeName = $request->query->get('name');
        $levels = $this->getLevelService()->searchLevels(
            array('likeName' => $likeName),
            array('createdTime' => 'ASC'),
            0,
            PHP_INT_MAX
        );

        return $this->createJsonResponse($levels);
    }

    public function teacherProfessionFieldMatchAction(Request $request)
    {
        $excludeIds = array();
        $likeName = $request->query->get('name');
        $userId = $request->query->get('userId');
        if (!empty($userId)) {
            $profile = $this->getProfileService()->getProfileByUserId($userId);
            $excludeIds = $profile['teacherProfessionFieldIds'];
        }

        $teacherProfessionFields = $this->getTeacherProfessionFieldService()->searchTeacherProfessionFields(array('likeName' => $likeName, 'excludeIds' => $excludeIds), array('seq' => 'ASC'), 0, PHP_INT_MAX);

        return $this->createJsonResponse($teacherProfessionFields);
    }

    public function settingAction(Request $request, $userId)
    {
        $profile = $this->getProfileService()->getProfileByUserId($userId);
        if ('POST' == $request->getMethod()) {
            $fields = $request->request->all();
            if (empty($fields['teacherProfessionFieldIds']) && empty($fields['levelId'])) {
                return $this->createJsonResponse(array('success' => false, 'message' => '请先设置等级和领域'));
            }
            $fields['teacherProfessionFieldIds'] = explode(',', $fields['teacherProfessionFieldIds']);
            $fields['levelId'] = empty($fields['levelId']) ? 0 : $fields['levelId'];

            $this->getProfileService()->batchSetTeacherProfile(array($userId), $fields);

            return $this->createJsonResponse(array('success' => true, 'message' => ''));
        }

        return $this->render('admin/teacher/teacher-setting-modal.html.twig', array(
            'profile' => $profile,
            'userId' => $userId,
            'level' => empty($profile['levelId']) ? 0 : $this->getLevelService()->getLevel($profile['levelId']),
            'professions' => empty($profile['teacherProfessionFieldIds']) ? array() : $this->getTeacherProfessionFieldService()->searchTeacherProfessionFields(array('ids' => $profile['teacherProfessionFieldIds']), array('seq' => 'Asc'), 0, PHP_INT_MAX),
        ));
    }

    public function batchSettingAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $ids = $request->request->get('ids');
            $fields = $request->request->all();
            if (empty($fields['teacherProfessionFieldIds']) && empty($fields['levelId'])) {
                return $this->createJsonResponse(array('success' => false, 'message' => '请先设置等级和领域'));
            }
            $ids = explode(',', $ids);
            $fields['teacherProfessionFieldIds'] = explode(',', $fields['teacherProfessionFieldIds']);
            $fields['levelId'] = empty($fields['levelId']) ? 0 : $fields['levelId'];

            $this->getProfileService()->batchSetTeacherProfile($ids, $fields);

            return $this->createJsonResponse(array('success' => true));
        }

        return $this->render('admin/teacher/teacher-batch-setting-modal.html.twig', array(
        ));
    }

    public function teacherCourseArchivesAction(Request $request, $userId)
    {
        $conditions = $request->query->all();
        $from = empty($conditions['from']) ? 'teacher-list' : $conditions['from'];
        $user = $this->getUserService()->getUser($userId);
        $conditions = $this->buildTeacherCourseArchivesConditions($conditions);
        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseSetService()->countUserTeachingCourseSets($user['id'], $conditions),
            20
        );
        $courseSets = $this->getCourseSetService()->searchUserTeachingCourseSets(
            $userId,
            $conditions,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        foreach ($courseSets as &$courseSet) {
            $courseSet['learnTime'] = $this->getTaskResultService()->sumLearnTimeByCourseId($courseSet['defaultCourseId']);
            $courseSet['learnedStudentNum'] = $this->getCourseMemberService()->countLearnedStudentsByCourseId($courseSet['defaultCourseId']);
            if ($this->isPluginInstalled('Survey')) {
                $courseSet['surveyScore'] = $this->getSurveyResultService()->getOnlineCourseSurveyScoreByCourseId($courseSet['defaultCourseId']);
            }
            $courseSet['averageLearnTime'] = $courseSet['studentNum'] > 0 ? sprintf('%.2f', $courseSet['learnTime'] / $courseSet['studentNum']) : 0;
        }

        $categories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($courseSets, 'categoryId'));
        $allCourseSets = $this->getCourseSetService()->searchUserTeachingCourseSets(
            $userId,
            $conditions,
            0,
            PHP_INT_MAX
        );
        $courseIds = ArrayToolkit::column($allCourseSets, 'defaultCourseId');

        $courseTotalScore = 0;
        $teacherTotalScore = 0;
        if ($this->isPluginInstalled('Survey')) {
            $courseTotalScore = $this->getSurveyResultService()->getOnlineCoursesAverageSurveyScore($courseIds);
            $teacherTotalScore = $this->getSurveyResultService()->getTeacherOnlineAndOfflineCoursesAverageSurveyScore($userId);
        }

        return $this->render('admin/teacher/teacher-archives/course.html.twig', array(
            'user' => $user,
            'courseSets' => $courseSets,
            'categories' => $categories,
            'paginator' => $paginator,
            'courseTime' => $conditions['courseTime'],
            'surveyScore' => $teacherTotalScore,
            'courseTotalScore' => $courseTotalScore,
            'courseCount' => count($allCourseSets),
            'from' => $from,
        ));
    }

    public function teacherOfflineCourseArchivesAction(Request $request, $userId)
    {
        $conditions = $request->query->all();
        $from = empty($conditions['from']) ? 'teacher-list' : $conditions['from'];
        $user = $this->getUserService()->getUser($userId);
        $conditions = $this->buildTeacherOfflineCourseArchivesConditions($conditions, $userId);
        $paginator = new Paginator(
            $this->get('request'),
            $this->getOfflineCourseService()->countOfflineCourses($conditions),
            20
        );
        $allOfflineCourses = $this->getOfflineCourseService()->searchOfflineCourses(
            $conditions,
            array('createdTime' => 'DESC'),
            0,
            PHP_INT_MAX
        );

        $offlineCourses = $this->getOfflineCourseService()->searchOfflineCourses(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        foreach ($offlineCourses as &$offlineCourse) {
            if ($this->isPluginInstalled('Survey')) {
                $offlineCourse['surveyScore'] = $this->getSurveyResultService()->getOfflineCourseSurveyScoreByCourseIdAndProjectPlanId($offlineCourse['id'], $offlineCourse['projectPlanId']);
            }
            $offlineCourse = $this->getOfflineCourseService()->buildOfflineCourseStatistic($offlineCourse);
        }

        $teacherTotalScore = 0;
        $courseTotalScore = 0;
        if ($this->isPluginInstalled('Survey')) {
            $courseTotalScore = $this->getSurveyResultService()->getOfflineCoursesAverageSurveyScore(ArrayToolkit::column($allOfflineCourses, 'id'));
            $teacherTotalScore = $this->getSurveyResultService()->getTeacherOnlineAndOfflineCoursesAverageSurveyScore($userId);
        }

        return $this->render('admin/teacher/teacher-archives/offline-course.html.twig', array(
            'user' => $user,
            'offlineCourses' => $offlineCourses,
            'paginator' => $paginator,
            'courseTime' => $conditions['courseTime'],
            'surveyScore' => $teacherTotalScore,
            'courseTotalScore' => $courseTotalScore,
            'courseCount' => count($allOfflineCourses),
            'from' => $from,
        ));
    }

    public function teacherListExporterAction(Request $request)
    {
        $conditions = $request->query->all();
        $conditions = $this->buildConditions($conditions);

        return $this->render('admin/teacher/teacher-profile-export.html.twig', array(
            'conditions' => json_encode($conditions),
        ));
    }

    public function teacherCourseExporterAction(Request $request)
    {
        $conditions = $request->query->all();
        $conditions = $this->buildTeacherCourseArchivesConditions($conditions);

        return $this->render('admin/teacher/teacher-archives/teacher-course-export.html.twig', array(
            'conditions' => json_encode($conditions),
        ));
    }

    public function teacherOfflineCourseExporterAction(Request $request)
    {
        $conditions = $request->query->all();
        $conditions = $this->buildTeacherOfflineCourseArchivesConditions($conditions, $conditions['userId']);

        return $this->render('admin/teacher/teacher-archives/teacher-offline-course-export.html.twig', array(
            'conditions' => json_encode($conditions),
        ));
    }

    protected function buildTeacherCourseArchivesConditions($conditions)
    {
        $courseTime = array('startTime' => strtotime(date('Y').'/01/01 00:00'), 'endTime' => time());

        if (!empty($conditions['courseCreateTime'])) {
            $courseCreateTime = explode('-', $conditions['courseCreateTime']);
            $courseTime['startTime'] = strtotime($courseCreateTime[0]);
            $courseTime['endTime'] = strtotime($courseCreateTime[1].' 23:59:59');
        }

        $conditions = array_merge($conditions, $courseTime);
        $conditions['courseTime'] = $courseTime;

        if (!empty($conditions['categoryId'])) {
            $categoryIds = $this->getCategoryService()->findCategoryChildrenIds($conditions['categoryId']);
            $categoryIds[] = $conditions['categoryId'];
            $conditions['categoryIds'] = $categoryIds;
            unset($conditions['categoryId']);
        }

        return $conditions;
    }

    protected function buildTeacherOfflineCourseArchivesConditions($conditions, $userId)
    {
        $courseTime = array('startTime' => strtotime(date('Y').'/01/01 00:00'), 'endTime' => time());
        if (!empty($conditions['courseCreateTime'])) {
            $courseCreateTime = explode('-', $conditions['courseCreateTime']);
            $courseTime['startTime'] = strtotime($courseCreateTime[0]);
            $courseTime['endTime'] = strtotime($courseCreateTime[1].' 23:59:59');
        }

        $conditions = array_merge($conditions, $courseTime, array('teacherId' => $userId));
        $conditions['courseTime'] = $courseTime;

        return $conditions;
    }

    protected function buildTeacherProfile($users, $conditions)
    {
        foreach ($users as &$user) {
            $courseSets = $this->getCourseSetService()->searchUserTeachingCourseSets(
                $user['id'],
                array_merge(array('excludeStatus' => array('draft')), $conditions['courseCreateTime']),
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
                $user['offlineCourseNum'] = count($offlineCourses);
                $projectPlanIds = empty($offlineCourses) ? array(-1) : ArrayToolkit::column($offlineCourses, 'projectPlanId');
                $user['offlineCourseStudentNum'] = $this->getProjectPlanMemberService()->countProjectPlanMembers(array('projectPlanIds' => $projectPlanIds));
            }

            if ($this->isPluginInstalled('Survey')) {
                $courseIds = ArrayToolkit::column($courseSets, 'defaultCourseId');
                $user['courseSurveyScore'] = $this->getSurveyResultService()->getOnlineCoursesAverageSurveyScore($courseIds);
                $user['offlineCourseSurveyScore'] = $this->getSurveyResultService()->getOfflineCoursesAverageSurveyScore(ArrayToolkit::column($offlineCourses, 'id'));
            }
        }

        return $users;
    }

    protected function buildConditions($conditions)
    {
        if (!empty($conditions['likeTeacherProfessionFieldIds']) || !empty($conditions['levelId'])) {
            $conditions['likeTeacherProfessionFieldIds'] = empty($conditions['likeTeacherProfessionFieldIds']) ? '' : '|'.$conditions['likeTeacherProfessionFieldIds'].'|';
            $teachers = $this->getProfileService()->searchProfiles($conditions, array(), 0, PHP_INT_MAX);
            $conditions['userIds'] = empty($teachers) ? array(-1) : ArrayToolkit::column($teachers, 'userId');
        }

        $conditions = $this->fillOrgCode($conditions);
        $conditions['roles'] = 'ROLE_TEACHER';

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

    protected function getUserOrgService()
    {
        return $this->createService('CorporateTrainingBundle:User:UserOrgService');
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
     * @return \CorporateTrainingBundle\Biz\Course\Service\Impl\CourseServiceImpl
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\Course\Service\Impl\CourseSetServiceImpl
     */
    protected function getCourseSetService()
    {
        return $this->createService('CorporateTrainingBundle:Course:CourseSetService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\Course\Service\Impl\MemberServiceImpl
     */
    protected function getCourseMemberService()
    {
        return $this->createService('CorporateTrainingBundle:Course:MemberService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\Task\Service\Impl\TaskResultServiceImpl
     */
    protected function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
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

    protected function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineCourse\Service\Impl\TaskServiceImpl
     */
    protected function getOfflineCourseTaskService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineCourse:TaskService');
    }
}
