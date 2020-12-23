<?php

namespace CorporateTrainingBundle\Controller\ProjectPlan;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;

class ProjectPlanStudyDataController extends BaseController
{
    public function studyDataOverviewAction($id)
    {
        $user = $this->getCurrentUser();
        $projectPlan = $this->getProjectPlanService()->getProjectPlan($id);
        $hasAdminDataCenterPermission = $user->hasManagePermissionWithOrgCode($projectPlan['orgCode']) && $user->hasPermission('admin_data_center_project_plan');
        $hasProjectPlanManagePermission = $this->getProjectPlanService()->canManageProjectPlan($id);
        if (!$hasProjectPlanManagePermission && !$hasAdminDataCenterPermission) {
            return $this->createMessageResponse('error', 'project_plan.message.can_not_manage_message');
        }

        $members = $this->getProjectPlanMemberService()->findMembersByProjectPlanId($id);
        $memberNum = count($members);

        $completionRate = $this->getCompletionRate($id, $members);
        $usersTotalLearnTime = $this->createProjectPlanStrategy()->calculateMembersTotalLearnTime($projectPlan, ArrayToolkit::column($members, 'userId'));

        $totalLearnTime = 0;
        foreach ($usersTotalLearnTime as $userTotalLearnTime) {
            $totalLearnTime += $userTotalLearnTime;
        }

        $completionRate['usersTotalLearnTime'] = round($totalLearnTime / 3600, 2);

        return $this->render('project-plan/study-data/statistic-chart.html.twig',
            array(
                'isEmpty' => $this->isStudyDataEmpty($id, $totalLearnTime),
                'projectPlan' => $projectPlan,
                'completionRate' => $completionRate,
                'averageLearnTime' => (0 != $memberNum) ? round($totalLearnTime / (3600 * $memberNum), 2) : 0,
                'hasProjectPlanManagePermission' => $hasProjectPlanManagePermission,
            ));
    }

    public function studyDataListAction(Request $request, $projectPlanId)
    {
        $user = $this->getCurrentUser();
        $projectPlan = $this->getProjectPlanService()->getProjectPlan($projectPlanId);
        $hasAdminDataCenterPermission = $user->hasManagePermissionWithOrgCode($projectPlan['orgCode']) && $user->hasPermission('admin_data_center_project_plan');
        $hasProjectPlanManagePermission = $this->getProjectPlanService()->canManageProjectPlan($projectPlanId);
        if (!$hasProjectPlanManagePermission && !$hasAdminDataCenterPermission) {
            return $this->createMessageResponse('error', 'project_plan.message.can_not_manage_message');
        }
        $renderData = $this->buildStudyListData($request, $projectPlan);

        $renderData['hasProjectPlanManagePermission'] = $hasProjectPlanManagePermission;

        return $this->render('project-plan/study-data/member-statistic-data.html.twig', $renderData);
    }

    public function studyDataAjaxListAction(Request $request, $projectPlanId)
    {
        $user = $this->getCurrentUser();
        if (!$this->getProjectPlanService()->canManageProjectPlan($projectPlanId) && !$user->hasPermission('admin_data_center_project_plan')) {
            return $this->createMessageResponse('error', 'project_plan.message.can_not_manage_message');
        }
        $projectPlan = $this->getProjectPlanService()->getProjectPlan($projectPlanId);

        $renderData = $this->buildStudyListData($request, $projectPlan, 'ajax');

        return $this->render('project-plan/study-data/member-statistic-data-list.html.twig', $renderData);
    }

    public function studyDataUserDetailAction(Request $request, $projectPlanId, $userId)
    {
        $user = $this->getCurrentUser();
        $projectPlan = $this->getProjectPlanService()->getProjectPlan($projectPlanId);
        $hasAdminDataCenterPermission = $user->hasManagePermissionWithOrgCode($projectPlan['orgCode']) && $user->hasPermission('admin_data_center_project_plan');
        $hasProjectPlanManagePermission = $this->getProjectPlanService()->canManageProjectPlan($projectPlanId);
        if (!$hasProjectPlanManagePermission && !$hasAdminDataCenterPermission) {
            return $this->createMessageResponse('error', 'project_plan.message.can_not_manage_message');
        }

        $onlineItems = $this->memberOnlineCourseStatistic($projectPlanId, $userId);
        $onlineCourseIds = ArrayToolkit::column($onlineItems, 'targetId');

        $offlineExamItems = $this->memberOfflineExamStatistic($projectPlanId, $userId);
        $offlineCourseItems = $this->memberOfflineCourseStatistic($projectPlanId, $userId);

        $onlineCourses = $this->getCourseService()->findCoursesByIds($onlineCourseIds);

        $user = $this->getUserService()->getUser($userId);
        $post = $this->getPostService()->getPost($user['postId']);
        $personalProgress = $this->getProjectPlanService()->getPersonalProjectPlanProgress($projectPlanId, $userId);
        $itemCount = $this->getProjectPlanService()->countProjectPlanItems(array('projectPlanId' => $projectPlanId));

        return $this->render('project-plan/study-data/index.html.twig', array(
                'onlineItems' => $onlineItems,
                'offlineCourseItems' => $offlineCourseItems,
                'offlineExamItems' => $offlineExamItems,
                'user' => $user,
                'courses' => $onlineCourses,
                'post' => $post,
                'projectPlan' => $projectPlan,
                'itemCount' => $itemCount,
                'personalProgress' => $personalProgress,
                'hasProjectPlanManagePermission' => $hasProjectPlanManagePermission,
            )
        );
    }

    protected function buildStudyListData($request, $projectPlan, $type = '')
    {
        $conditions = $request->request->all();
        $projectPlanId = $projectPlan['id'];
        $conditions = $this->prepareStudyDataListSearchConditions($conditions, $projectPlanId);

        $projectPlanItems = $this->getProjectPlanService()->findProjectPlanItemsByProjectPlanId($projectPlan['id']);
        $projectPlanItems = ArrayToolkit::group($projectPlanItems, 'targetType');

        $onlineCourseIds = isset($projectPlanItems['course']) ? ArrayToolkit::column($projectPlanItems['course'], 'targetId') : array(-1);

        $membersCount = $this->getProjectPlanMemberService()->countProjectPlanMembers(array('projectPlanId' => $projectPlanId));
        $projectPlanOfflineCourses = isset($projectPlanItems['offline_course']) ? $projectPlanItems['offline_course'] : array();
        $offlineCourseIds = !empty($projectPlanOfflineCourses) ? ArrayToolkit::column($projectPlanOfflineCourses, 'targetId') : array(-1);
        $offlineCourseData = $this->getOfflineCoursesData($projectPlanId);

        $paginator = new Paginator(
            $request,
            $this->getProjectPlanMemberService()->countProjectPlanMembers($conditions),
            20
        );
        if ('ajax' != $type) {
            $paginator->setBaseUrl($this->generateUrl('project_plan_study_data_ajax_list', array('projectPlanId' => $projectPlanId)));
        }

        $members = $this->getProjectPlanMemberService()->searchProjectPlanMembers(
            $conditions,
            array(),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        foreach ($members as &$member) {
            $member['progress'] = $this->getProjectPlanService()->getPersonalProjectPlanProgress($projectPlanId, $member['userId']);
            $member['finishedOnlineCourseNum'] = $this->getCourseMemberService()->countLearnedMember(array('m.userId' => $member['userId'], 'courseId' => $onlineCourseIds));
            $member['offlineCourseAttendNum'] = $this->getOfflineCourseTaskService()->countTaskResults(array('taskIds' => $offlineCourseData['offlineCoursesTaskIds'], 'attendStatus' => 'attended', 'userId' => $member['userId']));
            $member['offlineCoursePassedHomeworkNum'] = $this->getOfflineCourseTaskService()->countTaskResults(array('offlineCourseIds' => $offlineCourseIds, 'homeworkStatus' => 'passed', 'userId' => $member['userId']));
            $member['onlineCourseFinishedRate'] = (isset($projectPlanItems['course']) && 0 != count($projectPlanItems['course'])) ? round(($member['finishedOnlineCourseNum'] / count($projectPlanItems['course'])) * 100, 2) : 0;
            $member['offlineCourseAttendRate'] = (0 != $offlineCourseData['offlineCourseTaskCount']) ? round(($member['offlineCourseAttendNum'] / $offlineCourseData['offlineCourseTaskCount']) * 100, 2) : 0;
            $member['offlineCoursePassedHomeworkRate'] = (0 != $offlineCourseData['offlineCourseHasHomeWorkCount']) ? round(($member['offlineCoursePassedHomeworkNum'] / $offlineCourseData['offlineCourseHasHomeWorkCount']) * 100, 2) : 0;
        }

        $members = ArrayToolkit::index($members, 'id');

        $userIds = ArrayToolkit::column($members, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);
        $postIds = ArrayToolkit::column($users, 'postId');
        $posts = $this->getPostService()->findPostsByIds($postIds);
        $posts = ArrayToolkit::index($posts, 'id');

        $onlineCourseLearnTime = $this->getTaskResultService()->sumLearnTimeByCourseIdsAndUserIdsGroupByUserId($onlineCourseIds, $userIds);
        $onlineCourseLearnTime = ArrayToolkit::index($onlineCourseLearnTime, 'userId');

        return array(
            'onlineCourseLearnTime' => $onlineCourseLearnTime,
            'paginator' => $paginator,
            'orgIds' => implode(',', $conditions['orgIds']),
            'projectPlan' => $projectPlan,
            'members' => $members,
            'users' => $users,
            'posts' => $posts,
            'membersCount' => $membersCount,
        );
    }

    protected function isStudyDataEmpty($projectPlanId, $totalLearnTime)
    {
        $isEmpty = array('offlineCourse' => false, 'offlineExam' => false, 'exam' => false, 'onlineCourse' => false);
        $projectPlanMembers = $this->getProjectPlanMemberService()->findMembersByProjectPlanId($projectPlanId);
        $userIds = empty($projectPlanMembers) ? array(-1) : ArrayToolkit::column($projectPlanMembers, 'userId');
        $offlineItems = $this->getProjectPlanService()->findProjectPlanItemsByProjectPlanIdAndTargetType($projectPlanId, 'offline_course');
        $offlineCoursesIds = empty($offlineItems) ? array(-1) : ArrayToolkit::column($offlineItems, 'targetId');
        $offlineCourseTaskResults = $this->getOfflineCourseTaskService()->searchTaskResults(array('offlineCourseIds' => $offlineCoursesIds, 'userIds' => $userIds), array(), 0, 10);
        if (empty($offlineCourseTaskResults)) {
            $isEmpty['offlineCourse'] = true;
        }
        $offlineExamItems = $this->getProjectPlanService()->findProjectPlanItemsByProjectPlanIdAndTargetType($projectPlanId, 'offline_exam');
        $examIds = empty($offlineExamItems) ? array(-1) : ArrayToolkit::column($offlineExamItems, 'targetId');
        $offlineExamResults = $this->getOfflineExamMemberService()->searchMembers(array('offlineExamIds' => $examIds, 'userIds' => $userIds), array(), 0, 10);
        if (empty($offlineExamResults)) {
            $isEmpty['offlineExam'] = true;
        }
        if ($this->isPluginInstalled('Exam')) {
            $examItems = $this->getProjectPlanService()->findProjectPlanItemsByProjectPlanIdAndTargetType($projectPlanId, 'exam');
            $examIds = empty($examItems) ? array(-1) : ArrayToolkit::column($examItems, 'targetId');

            $examResults = $this->getExamService()->searchExamResults(array('examIds' => $examIds, 'userIds' => $userIds), array('score' => 'ASC'), 0, 10);
            if (empty($examResults)) {
                $isEmpty['exam'] = true;
            }
        }

        if (0 == $totalLearnTime) {
            $isEmpty['onlineCourse'] = true;
        }

        return $isEmpty;
    }

    protected function getOfflineCoursesData($projectPlanId)
    {
        $offlineItems = $this->getProjectPlanService()->findProjectPlanItemsByProjectPlanIdAndTargetType($projectPlanId, 'offline_course');
        $offlineCoursesIds = isset($offlineItems) ? ArrayToolkit::column($offlineItems, 'targetId') : array(-1);
        $offlineCoursesHasHomeWork = $this->getOfflineCourseTaskService()->searchTasks(array('offlineCourseIds' => $offlineCoursesIds, 'hasHomework' => 1), array(), 0, PHP_INT_MAX);
        $offlineCoursesTasks = $this->getOfflineCourseTaskService()->searchTasks(array('offlineCourseIds' => $offlineCoursesIds, 'type' => 'offlineCourse'), array(), 0, PHP_INT_MAX);
        $offlineCoursesTaskIds = ArrayToolkit::column($offlineCoursesTasks, 'id');
        $offlineCoursesTaskIds = !empty($offlineCoursesTaskIds) ? $offlineCoursesTaskIds : array(-1);

        return array('offlineCourseHasHomeWorkCount' => count($offlineCoursesHasHomeWork), 'offlineCourseTaskCount' => count($offlineCoursesTasks), 'offlineCoursesTaskIds' => $offlineCoursesTaskIds);
    }

    protected function memberOnlineCourseStatistic($projectPlanId, $userId)
    {
        $onlineItems = $this->getProjectPlanService()->findProjectPlanItemsByProjectPlanIdAndTargetType($projectPlanId, 'course');

        foreach ($onlineItems as &$item) {
            $courseMember = $this->getCourseMemberService()->getCourseMember($item['targetId'], $userId);
            $item['courseTaskNum'] = $this->getTaskService()->countTasks(array('courseId' => $item['targetId'], 'isOptional' => 0));
            $item['courseFinishTaskNum'] = $courseMember['learnedCompulsoryTaskNum'];
            $item['learnTime'] = $this->getTaskResultService()->sumLearnTimeByCourseIdAndUserId($item['targetId'], $userId);
        }

        return $onlineItems;
    }

    protected function prepareStudyDataListSearchConditions($conditions, $projectPlanId)
    {
        if (!isset($conditions['orgIds'])) {
            $orgs = $this->getOrgService()->findOrgsByPrefixOrgCodes(array('1.'));
            $orgIds = ArrayToolkit::column($orgs, 'id');
        }

        $conditions['orgIds'] = empty($orgIds) ? explode(',', $conditions['orgIds']) : $orgIds;
        $conditions['projectPlanId'] = $projectPlanId;
        $orgUserIds = $this->getProjectPlanOrgUserIds($projectPlanId, $conditions['orgIds']);
        $conditions['userIds'] = $orgUserIds;

        if (!empty($conditions['postId'])) {
            $users = $this->getUserService()->searchUsers(
                array('postId' => $conditions['postId']),
                array('id' => 'DESC'),
                0,
                PHP_INT_MAX
            );

            $userIds = ArrayToolkit::column($users, 'id');
            if (empty($conditions['userIds']) || empty($userIds)) {
                $conditions['userIds'] = array();
            } else {
                $conditions['userIds'] = array_intersect($conditions['userIds'], $userIds);
            }
        }

        if (!empty($conditions['username'])) {
            $userIds = $this->getUserService()->findUserIdsByNickNameOrTrueName($conditions['username']);
            if (empty($conditions['userIds']) || empty($userIds)) {
                $conditions['userIds'] = array();
            } else {
                $conditions['userIds'] = array_intersect($conditions['userIds'], $userIds);
            }

            unset($conditions['username']);
        }

        $conditions['userIds'] = empty($conditions['userIds']) ? array(-1) : $conditions['userIds'];

        return $conditions;
    }

    protected function getProjectPlanOrgUserIds($projectPlanId, $orgIds)
    {
        $members = $this->getProjectPlanMemberService()->findMembersByProjectPlanId($projectPlanId);

        $userIds = ArrayToolkit::column($members, 'userId');

        $users = $this->getUserOrgService()->searchUserOrgs(
            array('orgIds' => $orgIds, 'userIds' => $userIds),
            array(),
            0,
            PHP_INT_MAX
        );

        return ArrayToolkit::column($users, 'userId');
    }

    protected function memberOfflineExamStatistic($projectPlanId, $userId)
    {
        $offlineExamItems = $this->getProjectPlanService()->findProjectPlanItemsByProjectPlanIdAndTargetType($projectPlanId, 'offline_exam');
        $examIds = ArrayToolkit::column($offlineExamItems, 'targetId');
        $offlineExams = $this->getOfflineExamService()->findOfflineExamByIds($examIds);

        foreach ($offlineExams as &$offlineExam) {
            $offlineExam['result'] = $this->getOfflineExamMemberService()->getMemberByOfflineExamIdAndUserId($offlineExam['id'], $userId);
        }

        return $offlineExams;
    }

    protected function memberOfflineCourseStatistic($projectPlanId, $userId)
    {
        $offlineItems = $this->getProjectPlanService()->findProjectPlanItemsByProjectPlanIdAndTargetType($projectPlanId, 'offline_course');
        $offlineCourseIds = ArrayToolkit::column($offlineItems, 'targetId');
        $offlineCourses = $this->getOfflineCourseService()->findOfflineCoursesByIds($offlineCourseIds);

        foreach ($offlineCourses as &$offlineCourse) {
            $offlineCourse['taskCount'] = $this->getOfflineCourseTaskService()->countTasks(array('offlineCourseId' => $offlineCourse['id'], 'type' => 'offlineCourse'));
            $offlineCourse['hasHomework'] = $this->getOfflineCourseTaskService()->countTasks(array('offlineCourseId' => $offlineCourse['id'], 'hasHomework' => 1));
            $offlineCourse['attendCount'] = $this->getOfflineCourseTaskService()->countTaskResults(array('offlineCourseId' => $offlineCourse['id'], 'type' => 'offlineCourse', 'attendStatus' => 'attended', 'userId' => $userId));
            $offlineCourse['homeWorkPassCount'] = $this->getOfflineCourseTaskService()->countTaskResults(array('offlineCourseId' => $offlineCourse['id'], 'homeworkStatus' => 'passed', 'userId' => $userId));
        }

        return $offlineCourses;
    }

    protected function getCompletionRate($id, $members)
    {
        $completionRate = array();
        $memberNum = count($members);
        $userIds = empty($members) ? array(-1) : ArrayToolkit::column($members, 'userId');
        $projectPlanItems = $this->getProjectPlanService()->findProjectPlanItemsByProjectPlanId($id);
        $projectPlanItems = ArrayToolkit::group($projectPlanItems, 'targetType');

        $projectPlanOfflineCourses = isset($projectPlanItems['offline_course']) ? $projectPlanItems['offline_course'] : array();
        $offlineCourseIds = ArrayToolkit::column($projectPlanOfflineCourses, 'targetId');

        $projectPlanOnlineCourses = isset($projectPlanItems['course']) ? $projectPlanItems['course'] : array();
        $projectPlanCourseNum = count($projectPlanOnlineCourses);
        $onlineCourseIds = ArrayToolkit::column($projectPlanOnlineCourses, 'targetId');
        $allFinishedOnlineCourseNum = $this->getCourseMemberService()->countLearnedMember(array('userId' => $userIds, 'courseId' => $onlineCourseIds));

        $offlineCourseData = $this->getOfflineCoursesData($id);
        $allFinishedOfflineCourseAttendNum = $this->getOfflineCourseTaskService()->countTaskResults(array('offlineCourseIds' => $offlineCourseIds, 'attendStatus' => 'attended', 'userIds' => $userIds));
        $allOfflineCoursePassedHomeworkNum = $this->getOfflineCourseTaskService()->countTaskResults(array('offlineCourseIds' => $offlineCourseIds, 'homeworkStatus' => 'passed', 'userIds' => $userIds));

        $projectPlanOnlineExams = isset($projectPlanItems['exam']) ? $projectPlanItems['exam'] : array();
        $projectPlanOfflineExamNum = count($projectPlanOnlineExams);
        $onlineExamIds = ArrayToolkit::column($projectPlanOnlineExams, 'targetId');
        $projectPlanExamNum = count($projectPlanOnlineExams);
        $allExamPassedNum = 0;
        if ($this->isPluginInstalled('Exam')) {
            $allExamPassedNum = $this->getExamService()->countMembers(array('examIds' => $onlineExamIds, 'userIds' => $userIds, 'passStatus' => 'passed'));
        }

        $projectPlanOfflineExams = isset($projectPlanItems['offline_exam']) ? $projectPlanItems['offline_exam'] : array();
        $offlineExamIds = ArrayToolkit::column($projectPlanOfflineExams, 'targetId');
        $allOfflineExamPassedNum = $this->getOfflineExamMemberService()->countMembers(array('offlineExamIds' => $offlineExamIds, 'userIds' => $userIds, 'status' => 'passed'));

        if (0 != $memberNum) {
            $completionRate['hasHomeworkNum'] = $offlineCourseData['offlineCourseHasHomeWorkCount'];
            $completionRate['onlineCourseCompletionRate'] = (0 != $projectPlanCourseNum) ? $allFinishedOnlineCourseNum / ($projectPlanCourseNum * $memberNum) * 100 : 0;
            $completionRate['finishedAttendCompletionRate'] = (0 != $offlineCourseData['offlineCourseTaskCount']) ? $allFinishedOfflineCourseAttendNum / ($offlineCourseData['offlineCourseTaskCount'] * $memberNum) * 100 : 0;
            $completionRate['passedHomeworkCompletionRate'] = (0 != $offlineCourseData['offlineCourseHasHomeWorkCount']) ? $allOfflineCoursePassedHomeworkNum / ($offlineCourseData['offlineCourseHasHomeWorkCount'] * $memberNum) * 100 : 0;
            $completionRate['passExamCompletionRate'] = (0 != $projectPlanExamNum) ? $allExamPassedNum / ($projectPlanExamNum * $memberNum) * 100 : 0;
            $completionRate['passOfflineExamCompletionRate'] = (0 != $projectPlanOfflineExamNum) ? $allOfflineExamPassedNum / ($projectPlanOfflineExamNum * $memberNum) * 100 : 0;
        }

        return $completionRate;
    }

    protected function createProjectPlanStrategy()
    {
        return $this->getBiz()->offsetGet('projectPlan_item_strategy_context')->createStrategy('course');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\Impl\ProjectPlanServiceImpl
     */
    protected function getProjectPlanService()
    {
        return $this->createService('ProjectPlan:ProjectPlanService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\Impl\MemberServiceImpl
     */
    protected function getProjectPlanMemberService()
    {
        return $this->createService('ProjectPlan:MemberService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\Activity\Service\Impl\ActivityServiceImpl
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    protected function getTaskResultService()
    {
        return $this->createService('CorporateTrainingBundle:Task:TaskResultService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineCourse\Service\Impl\TaskServiceImpl
     */
    protected function getOfflineCourseTaskService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineCourse:TaskService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineCourse\Service\Impl\OfflineCourseServiceImpl
     */
    protected function getOfflineCourseService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineCourse:OfflineCourseService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineCourse\Service\Impl\MemberServiceImpl
     */
    protected function getOfflineCourseMemberService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineCourse:MemberService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineExam\Service\Impl\OfflineExamServiceImpl
     */
    protected function getOfflineExamService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineExam:OfflineExamService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineExam\Service\Impl\MemberServiceImpl
     */
    protected function getOfflineExamMemberService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineExam:MemberService');
    }

    protected function getPostService()
    {
        return $this->createService('CorporateTrainingBundle:Post:PostService');
    }

    protected function getUserOrgService()
    {
        return $this->createService('CorporateTrainingBundle:User:UserOrgService');
    }

    protected function getExamService()
    {
        return $this->createService('ExamPlugin:Exam:ExamService');
    }
}
