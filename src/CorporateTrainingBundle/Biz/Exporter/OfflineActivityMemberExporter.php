<?php

namespace CorporateTrainingBundle\Biz\Exporter;

use AppBundle\Common\ArrayToolkit;
use CorporateTrainingBundle\Common\OrgToolkit;

class OfflineActivityMemberExporter extends BaseOfflineActivityExporter
{
    public function getExportFileName()
    {
        return 'offline_activity_member.xls';
    }

    protected function buildExportData($parameters)
    {
        $conditions = $this->prepareSearchConditions($parameters);
        $members = $this->getMemberService()->searchMembers(
            $conditions,
            array('id' => 'asc'),
            0,
            PHP_INT_MAX
        );

        $userIds = ArrayToolkit::column($members, 'userId');

        list($users, $profiles, $orgs, $posts) = $this->buildUsersData($userIds);

        $memberData = array();

        foreach ($members as $member) {
            $memberData[] = array(
                $user = $users[$member['userId']],
                'truename' => empty($profiles[$member['userId']]) ? '-' : $profiles[$member['userId']]['truename'],
                'nickname' => empty($users[$member['userId']]) ? '-' : $users[$member['userId']]['nickname'],
                'org' => OrgToolkit::buildOrgsNames($user['orgIds'], $orgs),
                'post' => empty($posts[$users[$member['userId']]['postId']]) ? '-' : $posts[$users[$member['userId']]['postId']]['name'],
                'attendStatus' => $this->convertAttendStatus($member['attendedStatus']),
                'score' => $member['score'],
                'evaluate' => $member['evaluate'],
                'passStatus' => ('none' === $member['passedStatus']) ? $this->trans('offline_activity.examination_status.not_examined') : (('passed' === $member['passedStatus']) ? $this->trans('offline_activity.examination_status.passed') : $this->trans('offline_activity.examination_status.unpassed')),
            );
        }

        $activity = $this->getActivityService()->getOfflineActivity($parameters['activityId']);

        $exportData[] = array(
            'sheetName' => $activity['title'],
            'data' => $memberData,
        );

        return $exportData;
    }

    protected function convertAttendStatus($status)
    {
        if ('attended' == $status) {
            return $this->trans('offline_activity.student.attended');
        }

        if ('unattended' == $status) {
            return $this->trans('offline_activity.student.unattended');
        }

        return $this->trans('offline_activity.attend_status.unattend');
    }
}
