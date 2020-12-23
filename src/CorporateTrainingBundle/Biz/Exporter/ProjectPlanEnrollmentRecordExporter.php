<?php

namespace CorporateTrainingBundle\Biz\Exporter;

use AppBundle\Common\ArrayToolkit;
use CorporateTrainingBundle\Common\OrgToolkit;

class ProjectPlanEnrollmentRecordExporter extends AbstractExporter
{
    public function canExport($parameters)
    {
        return $this->getProjectPlanService()->canManageProjectPlan($parameters['projectPlanId']);
    }

    public function getExportFileName()
    {
        return 'project_plan_enrollment_record.xls';
    }

    public function getSortedHeadingRow($parameters)
    {
        return array(
            array('code' => 'truename', 'title' => $this->trans('student.profile.truename')),
            array('code' => 'nickname', 'title' => $this->trans('student.user_name')),
            array('code' => 'org', 'title' => $this->trans('student.profile.department')),
            array('code' => 'post', 'title' => $this->trans('student.profile.post')),
            array('code' => 'mobile', 'title' => $this->trans('student.profile.mobile')),
            array('code' => 'email', 'title' => $this->trans('student.profile.email')),
            array('code' => 'remark', 'title' => $this->trans('project_plan.advanced_option.require_remark')),
            array('code' => 'status', 'title' => $this->trans('study_center.my_offline_activity.review_status')),
        );
    }

    public function buildExportData($parameters)
    {
        $projectPlan = $this->getProjectPlanService()->getProjectPlan($parameters['projectPlanId']);
        $conditions = $this->prepareConditions($parameters);

        $records = $this->getProjectPlanMemberService()->searchEnrollmentRecords(
            $conditions,
            array('submittedTime' => 'DESC'),
            0,
            PHP_INT_MAX
        );
        $userIds = ArrayToolkit::column($records, 'userId');
        list($users, $profiles, $orgs, $posts) = $this->buildUsersData($userIds);

        $recordData = array();
        foreach ($records as $record) {
            $recordData[] = array(
                $user = empty($users[$record['userId']]) ? array() : $users[$record['userId']],
                'truename' => empty($profiles[$record['userId']]) ? '-' : $profiles[$record['userId']]['truename'],
                'nickname' => empty($user) ? '-' : $user['nickname'],
                'org' => OrgToolkit::buildOrgsNames($user['orgIds'], $orgs),
                'post' => empty($posts[$user['postId']]) ? '-' : $posts[$user['postId']]['name'],
                'mobile' => empty($profiles[$record['userId']]) ? '-' : $profiles[$record['userId']]['mobile'],
                'email' => empty($user) ? '-' : $user['email'],
                'remark' => $record['remark'],
                'status' => 'submitted' == $record['status'] ? $this->trans('project_plan.pending_review') : ('approved' == $record['status'] ? $this->trans('project_plan.status.passed') : $this->trans('project_plan.status.unpass')),
            );
        }

        $exportData[] = array(
            'sheetName' => $projectPlan['name'].$this->trans('project_plan.enrollment_verify'),
            'data' => $recordData,
        );

        return $exportData;
    }

    protected function prepareConditions($conditions)
    {
        if (!empty($conditions['username'])) {
            $userIds = $this->getUserService()->findUserIdsByNickNameOrTrueName($conditions['username']);
            if (empty($userIds)) {
                $conditions['userIds'] = array(-1);
            } else {
                $conditions['userIds'] = $userIds;
            }
            unset($conditions['username']);
        }
        if ('all' == $conditions['status']) {
            $conditions['excludeStatus'] = array('none');
            $conditions['status'] = '';
        }

        return $conditions;
    }

    protected function getProjectPlanMemberService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:MemberService');
    }
}
