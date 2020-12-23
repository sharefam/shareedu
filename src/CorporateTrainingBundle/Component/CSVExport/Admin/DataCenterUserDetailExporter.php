<?php

namespace CorporateTrainingBundle\Component\CSVExport\Admin;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Component\Export\Exporter;
use CorporateTrainingBundle\Common\DateToolkit;

class DataCenterUserDetailExporter extends Exporter
{
    public function canExport()
    {
        return true;
    }

    public function getTitles()
    {
        $titles = array(
            'user.fields.truename_label',
            'user.fields.username_label',
            'user_post',
            'user.profile.org',
        );

        $selectedUserLearnDataModules = $this->getSelectedUserLearnDataModules();

        foreach ($selectedUserLearnDataModules as $module) {
            $titles[] = $module['displayKey'];
        }

        return $titles;
    }

    public function getContent($start, $limit)
    {
        $users = $this->getUserService()->searchUsers(
            $this->conditions,
            array('id' => 'DESC'),
            $start,
            $limit
        );
        $userIds = ArrayToolkit::column($users, 'id');

        $selectedUserLearnDataModules = $this->getSelectedUserLearnDataModules();

        $userLearnData = $this->getUsersLearnData($userIds, $this->conditions, $selectedUserLearnDataModules);

        return $userLearnData;
    }

    public function getCount()
    {
        return $this->getUserService()->countUsers($this->conditions);
    }

    public function buildCondition($fields)
    {
        if (!empty($fields['dataSearchTime'])) {
            $dateSearchTime = explode('-', $fields['dataSearchTime']);
            $fields['startDateTime'] = strtotime($dateSearchTime[0]);
            $fields['endDateTime'] = strtotime($dateSearchTime[1].' 23:59:59');
        }

        return $this->prepareSearchConditions($fields);
    }

    protected function getPageConditions()
    {
        return array($this->parameter['start'], 500);
    }

    protected function getSelectedUserLearnDataModules()
    {
        $currentUser = $this->getUser();
        $userLearnDataModules = $this->container->get('corporatetraining.extension.manager')->getUserLearnDataModules();
        $customColumns['selected'] = $this->getUserService()->getUserCustomColumns($currentUser['id']);
        $customColumns['alternative'] = array_diff(array_keys($userLearnDataModules), $customColumns['selected']);

        $selectedUserLearnDataModules = $userLearnDataModules;
        $alternativeUserLearnDataModules = array();
        foreach ($customColumns['alternative'] as $alternativeColumn) {
            $alternativeUserLearnDataModules[$alternativeColumn] = $userLearnDataModules[$alternativeColumn];
            unset($selectedUserLearnDataModules[$alternativeColumn]);
        }

        if (isset($selectedUserLearnDataModules['post_course'])) {
            unset($selectedUserLearnDataModules['post_course']);
            $selectedUserLearnDataModules['post_course_progress'] = $userLearnDataModules['post_course_progress'];
            $selectedUserLearnDataModules['post_course_hours'] = $userLearnDataModules['post_course_hours'];
        }

        return $selectedUserLearnDataModules;
    }

    protected function prepareSearchConditions($fields)
    {
        $conditions['locked'] = 0;
        $conditions['noType'] = 'system';

        if (empty($fields['startDateTime']) || empty($fields['endDateTime'])) {
            list($startDateTime, $endDateTime) = DateToolkit::generateStartDateAndEndDate('year');
            $conditions['startDateTime'] = strtotime($startDateTime);
            $conditions['endDateTime'] = strtotime($endDateTime.' 23:59:59');
        } else {
            $conditions['startDateTime'] = $fields['startDateTime'];
            $conditions['endDateTime'] = $fields['endDateTime'];
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

    protected function fillOrgCode($conditions)
    {
        if (!isset($conditions['orgCode'])) {
            $conditions['likeOrgCode'] = $this->getUser()->getSelectOrgCode();
        } else {
            $conditions['likeOrgCode'] = $conditions['orgCode'];
            unset($conditions['orgCode']);
        }

        return $conditions;
    }

    protected function getUsersLearnData($userIds, $conditions, $userLearnModules)
    {
        if (empty($userIds)) {
            return array();
        }

        $date = array(
            'startDateTime' => $conditions['startDateTime'],
            'endDateTime' => $conditions['endDateTime'],
        );
        if (empty($date)) {
            return array();
        }

        $users = $this->getUserService()->findUsersByIds($userIds);
        $userProfiles = $this->getUserService()->findUserProfilesByIds($userIds);
        $postIds = ArrayToolkit::column($users, 'postId');
        $posts = $this->getPostService()->findPostsByIds($postIds);
        $posts = ArrayToolkit::index($posts, 'id');
        $orgIds = array();
        foreach ($users as $user) {
            $orgIds = array_merge($orgIds, $user['orgIds']);
        }
        $orgs = $this->getOrgService()->findOrgsByIds($orgIds);
        $orgs = ArrayToolkit::index($orgs, 'id');

        $learnModulesData = array();
        foreach ($userLearnModules as $key => $module) {
            $callable = array($this->getBiz()->service($module['service']), $module['method']);
            $learnModulesData[$key] = call_user_func($callable, array('userIds' => $userIds, 'date' => $date));
        }

        $userLearnData = array();
        foreach ($userIds as $userId) {
            $user = $users[$userId];
            $userProfile = $userProfiles[$userId];
            $userLearnData[$userId][] = $userProfile['truename'];
            $userLearnData[$userId][] = $user['nickname'];
            $userLearnData[$userId][] = empty($posts[$user['postId']]) ? '--' : $posts[$user['postId']]['name'];
            $userLearnData[$userId][] = $this->getOrgDisplayString($user['orgIds'], $orgs);

            foreach ($learnModulesData as $module => $learnModuleData) {
                $columnValue = isset($learnModuleData[$userId]) ? $learnModuleData[$userId] : '--';
                if (is_array($columnValue)) {
                    $columnValue = json_encode($columnValue, JSON_UNESCAPED_SLASHES);
                }
                $userLearnData[$userId][] = $columnValue."\t";
            }
        }

        return array_values($userLearnData);
    }

    protected function getOrgDisplayString($orgIds, $orgs)
    {
        $orgDisplayString = '';
        foreach ($orgIds as $orgId) {
            if ('' == $orgDisplayString) {
                $orgDisplayString = $orgs[$orgId]['name'].'('.$orgs[$orgId]['code'].')';
            } else {
                $orgDisplayString = $orgDisplayString.' | '.$orgs[$orgId]['name'].'('.$orgs[$orgId]['code'].')';
            }
        }

        return $orgDisplayString;
    }

    /**
     * @return PostService
     */
    protected function getPostService()
    {
        return $this->getBiz()->service('CorporateTrainingBundle:Post:PostService');
    }

    /**
     * @return \Biz\Org\Service\OrgService
     */
    protected function getOrgService()
    {
        return $this->getBiz()->service('Org:OrgService');
    }
}
