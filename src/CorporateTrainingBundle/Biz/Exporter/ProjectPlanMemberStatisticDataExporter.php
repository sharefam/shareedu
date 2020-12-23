<?php

namespace CorporateTrainingBundle\Biz\Exporter;

use AppBundle\Common\ArrayToolkit;
use CorporateTrainingBundle\Common\OrgToolkit;

class ProjectPlanMemberStatisticDataExporter extends AbstractExporter
{
    public function canExport($parameters)
    {
        $user = $this->biz['user'];
        if (!$user->hasPermission('admin_data_center_project_plan') && !$this->getProjectPlanService()->canManageProjectPlan($parameters['projectPlanId'])) {
            return false;
        }

        return true;
    }

    public function getExportFileName()
    {
        return 'project_plan_member_statistic_data.xls';
    }

    public function getSortedHeadingRow($parameters)
    {
        return array(
            array('code' => 'truename', 'title' => $this->trans('student.profile.truename')),
            array('code' => 'nickname', 'title' => $this->trans('student.user_name')),
            array('code' => 'org', 'title' => $this->trans('student.profile.department')),
            array('code' => 'post', 'title' => $this->trans('student.profile.post')),
            array('code' => 'progress', 'title' => $this->trans('project_plan.member.progress')),
            array('code' => 'onlineLearningTime', 'title' => $this->trans('project_plan.summary.study_data.online_learn_time')),
            array('code' => 'onlineProgress', 'title' => $this->trans('project_plan.study_data.online_completion_degree')),
            array('code' => 'attendance', 'title' => $this->trans('project_plan.study_data.offline_attendance_rate')),
            array('code' => 'homeworkPass', 'title' => $this->trans('project_plan.study_data.homework_passing_rate')),
        );
    }

    public function buildExportData($parameters)
    {
        $projectPlan = $this->getProjectPlanService()->getProjectPlan($parameters['projectPlanId']);
        $projectPlanItems = $this->getProjectPlanService()->findProjectPlanItemsByProjectPlanId($projectPlan['id']);
        $projectPlanItems = ArrayToolkit::group($projectPlanItems, 'targetType');

        $conditions = $this->prepareStudyDataListSearchConditions($parameters, $parameters['projectPlanId']);

        $members = $this->getProjectPlanMemberService()->searchProjectPlanMembers($conditions, array(), 0, PHP_INT_MAX);
        $userIds = ArrayToolkit::column($members, 'userId');
        list($users, $userProfiles, $orgs, $posts) = $this->buildUsersData($userIds);

        $onlineCourseIds = (isset($projectPlanItems['course'])) ? ArrayToolkit::column($projectPlanItems['course'], 'targetId') : array(-1);
        $offlineCourseIds = (isset($projectPlanItems['offline_course'])) ? ArrayToolkit::column($projectPlanItems['offline_course'], 'targetId') : array(-1);

        $offlineCourseData = $this->getProjectPlanOfflineCoursesData($parameters['projectPlanId']);
        $onlineCourseLearnTime = $this->getTaskResultService()->sumLearnTimeByCourseIdsAndUserIdsGroupByUserId($onlineCourseIds, $userIds);
        $onlineCourseLearnTime = ArrayToolkit::index($onlineCourseLearnTime, 'userId');

        $memberData = array();
        foreach ($members as $member) {
            $finishedOnlineCourseNum = $this->getCourseMemberService()->countLearnedMember(array('m.userId' => $member['userId'], 'courseId' => $onlineCourseIds));
            $offlineCourseAttendNum = $this->getOfflineCourseTaskService()->countTaskResults(array('taskIds' => $offlineCourseData['offlineCoursesTaskIds'], 'attendStatus' => 'attended', 'userId' => $member['userId']));
            $offlineCoursePassedHomeworkNum = $this->getOfflineCourseTaskService()->countTaskResults(array('offlineCourseIds' => $offlineCourseIds, 'homeworkStatus' => 'passed', 'userId' => $member['userId']));
            $memberData[] = array(
                $user = $users[$member['userId']],
                'truename' => empty($userProfiles[$user['id']]) ? '-' : $userProfiles[$member['userId']]['truename'],
                'nickname' => empty($user) ? '-' : $user['nickname'],
                'org' => OrgToolkit::buildOrgsNames($user['orgIds'], $orgs),
                'post' => empty($user['postId']) ? '-' : $posts[$user['postId']]['name'],
                'progress' => $this->getProjectPlanService()->getPersonalProjectPlanProgress($projectPlan['id'], $user['id']).'%',
                'onlineLearningTime' => empty($onlineCourseLearnTime[$user['id']]['totalLearnTime']) ? '--' : substr(sprintf('%.2f', $onlineCourseLearnTime[$user['id']]['totalLearnTime'] / 3600), 0, -1),
                'onlineProgress' => empty($finishedOnlineCourseNum) ? '--' : (int) (count($projectPlanItems['course']) ? ($finishedOnlineCourseNum / count($projectPlanItems['course']) * 100) : 0).'%',
                'attendance' => empty($offlineCourseAttendNum) ? '--' : (int) ($offlineCourseData['offlineCourseTaskCount'] ? ($offlineCourseAttendNum / $offlineCourseData['offlineCourseTaskCount'] * 100) : 0).'%',
                'homeworkPass' => empty($offlineCoursePassedHomeworkNum) ? '--' : (int) ($offlineCourseData['offlineCourseHasHomeWorkCount'] ? ($offlineCoursePassedHomeworkNum / $offlineCourseData['offlineCourseHasHomeWorkCount'] * 100) : 0).'%',
            );
        }

        $exportData[] = array(
            'sheetName' => $projectPlan['name'].$this->trans('project_plan.summary.study_data'),
            'data' => $memberData,
        );

        return $exportData;
    }

    protected function getProjectPlanOfflineCoursesData($projectPlanId)
    {
        $offlineItems = $this->getProjectPlanService()->findProjectPlanItemsByProjectPlanIdAndTargetType($projectPlanId, 'offline_course');
        $offlineCoursesIds = ArrayToolkit::column($offlineItems, 'targetId');
        $offlineCoursesHasHomeWork = $this->getOfflineCourseTaskService()->searchTasks(array('offlineCourseIds' => $offlineCoursesIds, 'hasHomework' => 1), array(), 0, PHP_INT_MAX);
        $offlineCoursesTasks = $this->getOfflineCourseTaskService()->searchTasks(array('offlineCourseIds' => $offlineCoursesIds, 'type' => 'offlineCourse'), array(), 0, PHP_INT_MAX);
        $offlineCoursesTaskIds = ArrayToolkit::column($offlineCoursesTasks, 'id');
        $offlineCoursesTaskIds = !empty($offlineCoursesTaskIds) ? $offlineCoursesTaskIds : array(-1);

        return array('offlineCourseHasHomeWorkCount' => count($offlineCoursesHasHomeWork), 'offlineCourseTaskCount' => count($offlineCoursesTasks), 'offlineCoursesTaskIds' => $offlineCoursesTaskIds);
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

    protected function prepareStudyDataListSearchConditions($conditions, $projectPlanId)
    {
        if (!empty($conditions['orgIds'])) {
            $conditions['orgIds'] = empty($orgIds) ? explode(',', $conditions['orgIds']) : $orgIds;

            $orgUserIds = $this->getAssignmentOrgUserIds($conditions['projectPlanId'], $conditions['orgIds']);
            $conditions['userIds'] = $orgUserIds;
        }

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

    protected function getAssignmentOrgUserIds($projectPlanId, $orgIds)
    {
        $members = $this->getProjectPlanMemberService()->findMembersByprojectPlanId($projectPlanId);

        $userIds = ArrayToolkit::column($members, 'userId');

        $users = $this->getUserOrgService()->searchUserOrgs(
            array('orgIds' => $orgIds, 'userIds' => $userIds),
            array(),
            0,
            PHP_INT_MAX
        );

        return ArrayToolkit::column($users, 'userId');
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

    /**
     * @return ExamService
     */
    protected function getExamService()
    {
        return $this->createService('ExamPlugin:Exam:ExamService');
    }

    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    protected function getTaskResultService()
    {
        return $this->createService('CorporateTrainingBundle:Task:TaskResultService');
    }

    protected function getOfflineCourseHomeworkService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:OfflineCourseHomeworkService');
    }

    protected function getTestPaperService()
    {
        return $this->createService('ExamPlugin:TestPaper:TestPaperService');
    }

    protected function getOfflineCourseActivityService()
    {
        return $this->createService('CorporateTrainingBundle:Activity:OfflineCourseActivityService');
    }

    protected function getOfflineExamResultService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:OfflineExamResultService');
    }

    protected function getOfflineCourseAttendanceService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:OfflineCourseAttendanceService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineCourse\Service\Impl\TaskServiceImpl
     */
    protected function getOfflineCourseTaskService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineCourse:TaskService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\Course\Service\Impl\MemberServiceImpl
     */
    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }
}
