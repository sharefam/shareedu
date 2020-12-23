<?php

namespace CorporateTrainingBundle\Biz\Exporter;

use AppBundle\Common\ArrayToolkit;
use CorporateTrainingBundle\Common\DateToolkit;

class DataCenterUserDetailExporter extends AbstractExporter
{
    public function canExport($parameters)
    {
        return true;
    }

    public function getExportFileName()
    {
        return 'data_center_user_detail.xls';
    }

    public function getSortedHeadingRow($parameters)
    {
        $row = array(
            array('code' => 'truename', 'title' => $this->trans('student.profile.truename')),
            array('code' => 'nickname', 'title' => $this->trans('student.user_name')),
        );

        $userLearnDataModules = $this->getUserLearnDataModules($this->biz['user']['id']);

        foreach ($userLearnDataModules as $key => $column) {
            if ('post_course' == $key) {
                $row[] = array('code' => 'postCourseProgress', 'title' => $this->trans('my.department.user_learn_data.post_course_process'));
                $row[] = array('code' => 'postCourseLearnTime', 'title' => $this->trans('my.department.user_learn_data.post_course_learn_time'));
            } else {
                $row[] = array('code' => $key, 'title' => $this->trans($column['displayKey']));
            }
        }

        return $row;
    }

    public function buildExportData($parameters)
    {
        $conditions = $this->prepareSearchConditions($parameters);

        $userLearnDataModules = $this->getUserLearnDataModules($this->biz['user']['id']);

        $usersLearnData = $this->getUsersLearnData($conditions, $userLearnDataModules);

        $exportData[] = array(
            'data' => $usersLearnData,
        );

        return $exportData;
    }

    protected function getUserLearnDataModules($userId)
    {
        $userLearnDataModules = $this->getServiceContainer()->get('corporatetraining.extension.manager')->getUserLearnDataModules();
        $userCustomColumns = $this->getUserService()->getUserCustomColumns($userId);
        $notSelectedCustomColumns = array_diff(array_keys($userLearnDataModules), $userCustomColumns);

        foreach ($notSelectedCustomColumns as $notSelectedCustomColumn) {
            unset($userLearnDataModules[$notSelectedCustomColumn]);
        }

        return $userLearnDataModules;
    }

    protected function prepareSearchConditions($fields)
    {
        $conditions['locked'] = 0;
        $conditions['noType'] = 'system';

        if (empty($fields['dataSearchTime'])) {
            list($startDateTime, $endDateTime) = DateToolkit::generateStartDateAndEndDate('year');
            $conditions['startDateTime'] = strtotime($startDateTime);
            $conditions['endDateTime'] = strtotime($endDateTime.' 23:59:59');
        } else {
            $dataSearchTime = explode('-', $fields['dataSearchTime']);
            $conditions['startDateTime'] = strtotime($dataSearchTime[0]);
            $conditions['endDateTime'] = strtotime($dataSearchTime[1].' 23:59:59');
        }

        if (!empty($fields['orgCode'])) {
            $conditions['orgCode'] = $fields['orgCode'];
        }

        $conditions = $this->fillOrgCode($conditions);

        if (!empty($fields['postId'])) {
            $conditions['postId'] = $fields['postId'];
        }

        if (!empty($fields['hireDateSearchTime'])) {
            $hireDateSearchTime = explode('-', $fields['hireDateSearchTime']);
            $conditions['hireDate_GTE'] = strtotime($hireDateSearchTime[0]);
            $conditions['hireDate_LTE'] = strtotime($hireDateSearchTime[1].' 23:59:59');
        }

        if (!empty($fields['keyword'])) {
            $conditions[$fields['keywordType']] = $fields['keyword'];
        }

        return $conditions;
    }

    protected function getUsersLearnData($conditions, $userLearnModules)
    {
        $users = $this->getUserService()->searchUsers($conditions, array(), 0, PHP_INT_MAX);
        $userIds = ArrayToolkit::column($users, 'id');

        $date = array(
            'startDateTime' => $conditions['startDateTime'],
            'endDateTime' => $conditions['endDateTime'],
        );
        if (empty($date)) {
            return array();
        }

        $learnModulesData = array();
        foreach ($userLearnModules as $key => $module) {
            $callable = array($this->createService($module['service']), $module['method']);
            $learnModulesData[$key] = call_user_func($callable, array('userIds' => $userIds, 'date' => $date));
        }

        $userLearnData = array();

        $userProfiles = $this->getUserService()->findUserProfilesByIds($userIds);
        foreach ($userIds as $userId) {
            $user = $users[$userId];
            $userProfile = $userProfiles[$userId];
            $userLearnData[$userId]['truename'] = empty($userProfile['truename']) ? '--' : $userProfile['truename'];
            $userLearnData[$userId]['nickname'] = $user['nickname'];
            foreach ($learnModulesData as $module => $learnModuleData) {
                if ('post_course' == $module) {
                    $userLearnData[$userId]['postCourseProgress'] = $learnModuleData[$userId]['progress'];
                    $userLearnData[$userId]['postCourseLearnTime'] = $learnModuleData[$userId]['learnHours'];
                } else {
                    $userLearnData[$userId][$module] = isset($learnModuleData[$userId]) ? $learnModuleData[$userId] : 0;
                }
            }
        }

        return array_values($userLearnData);
    }
}
