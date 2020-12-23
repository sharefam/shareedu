<?php

namespace CorporateTrainingBundle\Biz\Exporter;

use AppBundle\Common\ArrayToolkit;
use CorporateTrainingBundle\Common\OrgToolkit;

class OfflineActivityBatchGradeExampleExporter extends BaseOfflineActivityExporter
{
    public function getExportFileName()
    {
        return 'batch_grade_example.xls';
    }

    protected function buildExportData($parameters)
    {
        $conditions = $this->prepareSearchConditions($parameters);
        $members = $this->getMemberService()->searchMembers(
            $conditions,
            array('createdTime' => 'DESC'),
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
                'attendStatus' => '',
                'score' => '',
                'evaluate' => '',
                'passStatus' => '',
            );
        }

        $activity = $this->getActivityService()->getOfflineActivity($parameters['activityId']);

        $exportData[] = array(
            'sheetName' => $activity['title'],
            'data' => $memberData,
        );

        return $exportData;
    }
}
