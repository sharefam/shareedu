<?php

namespace CorporateTrainingBundle\Biz\Exporter;

use AppBundle\Common\ArrayToolkit;
use CorporateTrainingBundle\Common\OrgToolkit;

class ProjectPlanOfflineExamMemberExporter extends AbstractExporter
{
    public function canExport($parameters)
    {
        return $this->getProjectPlanService()->canManageProjectPlan($parameters['projectPlanId']);
    }

    public function getExportFileName()
    {
        return 'project_plan_offline_exam_member.xls';
    }

    public function getSortedHeadingRow($parameters)
    {
        return array(
            array('code' => 'truename', 'title' => $this->trans('student.profile.truename')),
            array('code' => 'nickname', 'title' => $this->trans('student.user_name')),
            array('code' => 'org', 'title' => $this->trans('student.profile.department')),
            array('code' => 'post', 'title' => $this->trans('student.profile.post')),
            array('code' => 'score', 'title' => $this->trans('project_plan.member.score.required')),
            array('code' => 'status', 'title' => $this->trans('project_plan.study_data.exam_result.unRequired')),
        );
    }

    public function buildExportData($parameters)
    {
        $members = $this->getProjectPlanMemberService()->searchProjectPlanMembers(
            $parameters,
            array('id' => 'DESC'),
            0,
            PHP_INT_MAX
        );

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
                'score' => '',
                'status' => '',
            );
        }

        $exportData[] = array(
            'data' => $memberData,
        );

        return $exportData;
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\MemberService
     */
    protected function getProjectPlanMemberService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:MemberService');
    }
}
