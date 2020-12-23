<?php

namespace CorporateTrainingBundle\Biz\Exporter;

use AppBundle\Common\ArrayToolkit;
use CorporateTrainingBundle\Common\OrgToolkit;

class ProjectPlanOfflineExamResultExporter extends AbstractExporter
{
    public function canExport($parameters)
    {
        return $this->getProjectPlanService()->canManageProjectPlan($parameters['projectPlanId']);
    }

    public function getExportFileName()
    {
        return 'project_plan_offline_exam_result.xls';
    }

    public function getSortedHeadingRow($parameters)
    {
        return array(
            array('code' => 'truename', 'title' => $this->trans('student.profile.truename')),
            array('code' => 'nickname', 'title' => $this->trans('student.user_name')),
            array('code' => 'org', 'title' => $this->trans('student.profile.department')),
            array('code' => 'post', 'title' => $this->trans('student.profile.post')),
            array('code' => 'score', 'title' => $this->trans('offline_activity.examination.score')),
            array('code' => 'status', 'title' => $this->trans('project_plan.study_data.exam_result')),
        );
    }

    public function buildExportData($parameters)
    {
        $conditions = $this->prepareConditions($parameters);

        $members = $this->getProjectPlanMemberService()->searchProjectPlanMembers(
            $conditions,
            array('id' => 'DESC'),
            0,
            PHP_INT_MAX
        );

        $examResults = $this->getOfflineExamMemberService()->searchMembers(array('offlineExamId' => $parameters['offlineExamId']), array(), 0, PHP_INT_MAX);
        $examResults = ArrayToolkit::index($examResults, 'userId');
        $userIds = ArrayToolkit::column($members, 'userId');
        list($users, $userProfiles, $orgs, $posts) = $this->buildUsersData($userIds);

        $memberData = array();
        foreach ($members as $member) {
            $memberData[] = array(
                $user = $users[$member['userId']],
                'truename' => empty($userProfiles[$member['userId']]) ? '-' : $userProfiles[$member['userId']]['truename'],
                'nickname' => empty($users[$member['userId']]) ? '-' : $users[$member['userId']]['nickname'],
                'org' => OrgToolkit::buildOrgsNames($user['orgIds'], $orgs),
                'post' => empty($posts[$users[$member['userId']]['postId']]) ? '-' : $posts[$users[$member['userId']]['postId']]['name'],
                'score' => empty($examResults[$member['userId']]) ? '-' : $examResults[$member['userId']]['score'],
                'status' => empty($examResults[$member['userId']]) ? $this->trans('project_plan.status.not_review') : $this->getExamStatus($examResults[$member['userId']]),
            );
        }

        $exportData[] = array(
            'data' => $memberData,
        );

        return $exportData;
    }

    protected function prepareConditions($conditions)
    {
        if (isset($conditions['status']) && 'all' == $conditions['status'] || !isset($conditions['status'])) {
            unset($conditions['status']);
        } else {
            $offlineExamResult = $this->getOfflineExamMemberService()->searchMembers($conditions, array(), 0, PHP_INT_MAX);
            $conditions['userIds'] = !empty($offlineExamResult) ? ArrayToolkit::column($offlineExamResult, 'userId') : array(-1);

            unset($conditions['status']);
        }

        if (isset($conditions['username']) && !empty($conditions['username'])) {
            $userIds = $this->getUserService()->findUserIdsByNickNameOrTrueName($conditions['username']);
            $userIds = !empty($conditions['userIds']) ? array_intersect($userIds, $conditions['userIds']) : $userIds;
            $conditions['userIds'] = !empty($userIds) ? $userIds : array(-1);

            unset($conditions['username']);
        }

        if (isset($conditions['orgIds']) && empty($conditions['orgIds'])) {
            $conditions['userIds'] = ($conditions['userIds'] = array(-1) || empty($conditions['userIds'])) ? array(-1) : $conditions['userIds'];
            $conditions['orgIds'] = $this->prepareOrgIds($conditions);
        } else {
            $conditions['orgIds'] = $this->prepareOrgIds($conditions);
            $userIds = $this->findUserIdsByOrgIds($conditions['orgIds']);
            $userIds = !empty($conditions['userIds']) ? array_intersect($userIds, $conditions['userIds']) : $userIds;
            $conditions['userIds'] = !empty($userIds) ? $userIds : array(-1);
        }

        return $conditions;
    }

    protected function findUserIdsByOrgIds($orgIds)
    {
        $usersOrg = $this->getUserOrgService()->findUserOrgsByOrgIds($orgIds);

        return ArrayToolkit::column($usersOrg, 'userId');
    }

    protected function prepareOrgIds($conditions)
    {
        if (isset($conditions['orgIds']) && empty($conditions['orgIds'])) {
            $conditions['orgIds'] = array(-1);

            return $conditions;
        }

        return explode(',', $conditions['orgIds']);
    }

    protected function getExamStatus($examResult)
    {
        switch ($examResult['status']) {
            case 'none':
                return $this->trans('project_plan.status.not_review');
            case 'passed':
                return $this->trans('project_plan.pass');
            case 'unpassed':
                return $this->trans('project_plan.status.unpass');
        }
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\MemberService
     */
    protected function getProjectPlanMemberService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:MemberService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineExam\Service\Impl\MemberServiceImpl
     */
    protected function getOfflineExamMemberService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineExam:MemberService');
    }
}
