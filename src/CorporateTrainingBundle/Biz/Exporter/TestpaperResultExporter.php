<?php

namespace CorporateTrainingBundle\Biz\Exporter;

use AppBundle\Common\ArrayToolkit;
use CorporateTrainingBundle\Common\OrgToolkit;

class TestpaperResultExporter extends AbstractExporter
{
    public function canExport($parameters)
    {
        $user = $this->biz['user'];

        return $user->hasPermission('admin_train_teach_manage_my_teaching_courses_manage') || $user->hasPermission('admin_course_manage');
    }

    public function getExportFileName()
    {
        return 'testpaper_result.xls';
    }

    protected function getSortedHeadingRow($parameters)
    {
        return array(
            array('code' => 'truename', 'title' => $this->trans('testpaper_result.export_field.truename')),
            array('code' => 'nickname', 'title' => $this->trans('testpaper_result.export_field.nickname')),
            array('code' => 'org', 'title' => $this->trans('testpaper_result.export_field.org')),
            array('code' => 'status', 'title' => $this->trans('testpaper_result.export_field.status')),
            array('code' => 'firstScore', 'title' => $this->trans('testpaper_result.export_field.firstScore')),
            array('code' => 'maxScore', 'title' => $this->trans('testpaper_result.export_field.maxScore')),
        );
    }

    protected function buildExportData($parameters)
    {
        $conditions = array(
            'testId' => $parameters['testpaperId'],
            'lessonId' => $parameters['activityId'],
        );

        $status = $parameters['status'];
        if (!in_array($status, array('all', 'finished', 'reviewing', 'doing'))) {
            $status = 'all';
        }

        if ('all' != $status) {
            $conditions['status'] = $status;
        }

        if (!empty($parameters['keyword'])) {
            $userIds = $this->getUserService()->findUserIdsByNickNameOrTrueName($parameters['keyword']);
            $conditions['userIds'] = !empty($userIds) ? $userIds : array(-1);
        }

        $testpaperResults = $this->getTestpaperService()->searchTestpaperResults(
            $conditions,
            array('endTime' => 'DESC'),
            0,
            PHP_INT_MAX
        );

        $userIds = ArrayToolkit::column($testpaperResults, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);
        $userProfiles = $this->getUserService()->findUserProfilesByIds($userIds);
        $userResults = $this->getTestpaperService()->findTestResultsByTestpaperIdAndUserIds($userIds, $parameters['testpaperId']);
        $testpaperResults = ArrayToolkit::group($testpaperResults, 'userId');

        $resultData = array();
        foreach ($users as $user) {
            $userProfile = $userProfiles[$user['id']];
            $orgs = $this->getOrgService()->findOrgsByIds($user['orgIds']);
            $userResult = $userResults[$user['id']];
            $resultData[] = array(
                'truename' => $userProfile['truename'],
                'nickname' => $user['nickname'],
                'org' => OrgToolkit::buildOrgsNames($user['orgIds'], $orgs),
                'status' => $this->getFinalStatus($testpaperResults[$user['id']]),
                'firstScore' => $userResult['firstScore'],
                'maxScore' => $userResult['maxScore'],
            );
        }

        $exportData[] = array(
            'data' => $resultData,
        );

        return $exportData;
    }

    private function getFinalStatus($results)
    {
        $allStatus = ArrayToolkit::column($results, 'status');
        if (in_array('finished', $allStatus)) {
            return $this->trans('testpaper_result.export_field.finished');
        }

        if (in_array('reviewing', $allStatus)) {
            return $this->trans('testpaper_result.export_field.reviewing');
        }

        return $this->trans('testpaper_result.export_field.doing');
    }
}
