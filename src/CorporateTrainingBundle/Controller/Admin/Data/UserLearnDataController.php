<?php

namespace CorporateTrainingBundle\Controller\Admin\Data;

use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Controller\Admin\BaseController;
use CorporateTrainingBundle\Biz\Course\Service\CourseSetService;
use CorporateTrainingBundle\Biz\OfflineActivity\Service\MemberService;
use CorporateTrainingBundle\Biz\OfflineActivity\Service\OfflineActivityService;
use CorporateTrainingBundle\Biz\ProjectPlan\Service\ProjectPlanService;
use CorporateTrainingBundle\Biz\Taxonomy\Service\CategoryService;
use ExamPlugin\Biz\Exam\Service\ExamService;
use CorporateTrainingBundle\Common\DateToolkit;
use SurveyPlugin\Biz\Survey\Service\SurveyService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserLearnDataController extends BaseController
{
    public function userLearnDataOverviewAction(Request $request)
    {
        list($weekStartDate, $weekEndDate) = DateToolkit::generateStartDateAndEndDate('week');
        $loginSearchTime['startDate'] = $weekStartDate;
        $loginSearchTime['endDate'] = $weekEndDate;

        list($yearStartDate, $yearEndDate) = DateToolkit::generateStartDateAndEndDate('year');
        $userPortraitSearchTime['startTime'] = $yearStartDate;
        $userPortraitSearchTime['endTime'] = $yearEndDate;

        return $this->render(
            'CorporateTrainingBundle::admin/data/user-learn/overview.html.twig',
            array(
                'loginSearchTime' => $loginSearchTime,
                'userPortraitSearchTime' => $userPortraitSearchTime,
            )
        );
    }

    public function userLearnDataDetailAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();

        $fields = $request->query->all();

        if (!empty($fields['dataSearchTime'])) {
            $dateSearchTime = explode('-', $fields['dataSearchTime']);
            $fields['startDateTime'] = strtotime($dateSearchTime[0]);
            $fields['endDateTime'] = strtotime($dateSearchTime[1].' 23:59:59');
        }

        $conditions = $this->prepareSearchConditions($fields);
        $count = $this->getUserService()->countUsers($conditions);
        $paginator = new Paginator(
            $this->get('request'),
            $count,
            10
        );
        $paginator->setBaseUrl($this->generateUrl('admin_user_learn_data_ajax_detail'));

        $users = $this->getUserService()->searchUsers(
            $conditions,
            array('id' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userIds = ArrayToolkit::column($users, 'id');

        $userLearnDataModules = $this->container->get('corporatetraining.extension.manager')->getUserLearnDataModules();
        $customColumns['selected'] = $this->getUserService()->getUserCustomColumns($currentUser['id']);
        $customColumns['alternative'] = array_diff(array_keys($userLearnDataModules), $customColumns['selected']);

        $selectedUserLearnDataModules = $userLearnDataModules;
        $alternativeUserLearnDataModules = array();
        foreach ($customColumns['alternative'] as $alternativeColumn) {
            $alternativeUserLearnDataModules[$alternativeColumn] = $userLearnDataModules[$alternativeColumn];
            unset($selectedUserLearnDataModules[$alternativeColumn]);
        }

        $selectedCustomColumnsDisplayData = $this->getCustomColumnsDisplayData($selectedUserLearnDataModules);
        $alternativeCustomColumnsDisplayData = $this->getCustomColumnsDisplayData($alternativeUserLearnDataModules);

        $userLearnData = $this->getUsersLearnData($userIds, $conditions, $selectedUserLearnDataModules);

        return $this->render('CorporateTrainingBundle::admin/data/user-learn/detail.html.twig',
            array(
                'userLearnData' => json_encode($userLearnData),
                'conditions' => $conditions,
                'selectedCustomColumns' => json_encode($selectedCustomColumnsDisplayData),
                'alternativeCustomColumns' => json_encode($alternativeCustomColumnsDisplayData),
                'userLearnModules' => $selectedUserLearnDataModules,
                'paginator' => $paginator,
                'tips' => json_encode($this->buildTableTips()),
            )
        );
    }

    public function ajaxSaveCustomColumnsAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();
        $params = $request->request->all();
        $this->getUserService()->updateUserCustomColumns($currentUser['id'], json_decode($params['data']));

        return new Response('success');
    }

    public function ajaxUserLearnDataDetailAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();

        $fields = $request->request->all();

        if (!empty($fields['dataSearchTime'])) {
            $dateSearchTime = explode('-', $fields['dataSearchTime']);
            $fields['startDateTime'] = strtotime($dateSearchTime[0]);
            $fields['endDateTime'] = strtotime($dateSearchTime[1].' 23:59:59');
        }

        $conditions = $this->prepareSearchConditions($fields);

        $count = $this->getUserService()->countUsers($conditions);
        $paginator = new Paginator(
            $this->get('request'),
            $count,
            10
        );

        $users = $this->getUserService()->searchUsers(
            $conditions,
            array('id' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userIds = ArrayToolkit::column($users, 'id');
        $userLearnDataModules = $this->container->get('corporatetraining.extension.manager')->getUserLearnDataModules();
        $customColumns['selected'] = $this->getUserService()->getUserCustomColumns($currentUser['id']);
        $customColumns['alternative'] = array_diff(array_keys($userLearnDataModules), $customColumns['selected']);

        $selectedUserLearnDataModules = $userLearnDataModules;
        foreach ($customColumns['alternative'] as $alternativeColumn) {
            unset($selectedUserLearnDataModules[$alternativeColumn]);
        }

        $userLearnData = $this->getUsersLearnData($userIds, $conditions, $selectedUserLearnDataModules);

        return $this->render('CorporateTrainingBundle::admin/data/user-learn/detail-tr.html.twig',
            array(
                'userLearnData' => json_encode($userLearnData),
                'tips' => json_encode($this->buildTableTips()),
                'paginator' => $paginator,
            )
        );
    }

    public function userLoginHeatmapAction(Request $request)
    {
        $fields = $request->query->all();

        $conditions = $this->prepareSearchConditions($fields);
        $conditions['startDate'] = $fields['dataSearchTime'];

        $data = array();
        for ($dayInWeek = 0; $dayInWeek < 7; ++$dayInWeek) {
            $date = date('Y-m-d', strtotime("+$dayInWeek days", strtotime($conditions['startDate'])));

            $dateHourData = $this->getDataStatisticsService()->statisticsAPPDailyLogin($conditions, $date);
            $maxLoginHourData[$dayInWeek] = $this->getMaxLoginHourData($dateHourData);

            if ('all' == $fields['type']) {
                $webDateHourData = $this->getDataStatisticsService()->statisticsWebDailyLogin($conditions, $date);
                $dateHourData = $this->sumDateHourData($dateHourData, $webDateHourData);
                $maxLoginHourData[$dayInWeek] = $this->getMaxLoginHourData($dateHourData);
            }

            $hourData = array();
            for ($hourInDay = 0; $hourInDay < 24; ++$hourInDay) {
                if (!isset($dateHourData[$hourInDay])) {
                    $hourData[$dayInWeek][$hourInDay] = array(
                        $dayInWeek,
                        $hourInDay,
                        0,
                    );
                } else {
                    $hourData[$dayInWeek][$hourInDay] = array(
                        $dayInWeek,
                        $hourInDay,
                        (int) $dateHourData[$hourInDay]['count'],
                    );
                }
            }
            $data = array_merge_recursive($data, $hourData[$dayInWeek]);
        }

        $LoginData = array(
            'loginHourData' => $data,
            'maxLoginHourData' => (int) max($maxLoginHourData),
        );

        return $this->createJsonResponse(json_encode($LoginData));
    }

    protected function getMaxLoginHourData($dateHourData)
    {
        $loginTimes = ArrayToolkit::column($dateHourData, 'count');

        if (empty($loginTimes)) {
            return 0;
        }

        return max($loginTimes);
    }

    public function userOnlineCourseLearnTimeAction(Request $request)
    {
        $fields = $request->request->all();
        if (!empty($fields['dataSearchTime'])) {
            $dateSearchTime = explode('-', $fields['dataSearchTime']);
            $fields['startDateTime'] = strtotime($dateSearchTime[0]);
            $fields['endDateTime'] = strtotime($dateSearchTime[1].' 23:59:59');
        }

        $conditions = $this->prepareSearchConditions($fields);
        $totalUserNum = $this->getDataStatisticsService()->statisticsUserNum($conditions);
        $totalLearnTime = $this->getDataStatisticsService()->statisticsOnlineCourseLearnTime($conditions);

        $avg = !empty($totalUserNum) ? round($totalLearnTime / ($totalUserNum * 3600), 1) : 0;

        return $this->createJsonResponse(array('data' => $avg));
    }

    public function userOfflineLearnTimeAction(Request $request)
    {
        $fields = $request->request->all();
        if (!empty($fields['dataSearchTime'])) {
            $dateSearchTime = explode('-', $fields['dataSearchTime']);
            $fields['startDateTime'] = strtotime($dateSearchTime[0]);
            $fields['endDateTime'] = strtotime($dateSearchTime[1].' 23:59:59');
        }
        $conditions = $this->prepareSearchConditions($fields);

        $totalUserNum = $this->getDataStatisticsService()->statisticsUserNum($conditions);
        $totalLearnTime = $this->getDataStatisticsService()->statisticsOfflineCourseLearnTime($conditions);

        $avg = !empty($totalUserNum) ? round($totalLearnTime / ($totalUserNum * 3600), 1) : 0;

        return $this->createJsonResponse(array('data' => $avg));
    }

    public function userOnlineCoursesLearnCountAction(Request $request)
    {
        $fields = $request->request->all();
        if (!empty($fields['dataSearchTime'])) {
            $dateSearchTime = explode('-', $fields['dataSearchTime']);
            $fields['startDateTime'] = strtotime($dateSearchTime[0]);
            $fields['endDateTime'] = strtotime($dateSearchTime[1].' 23:59:59');
        }
        $conditions = $this->prepareSearchConditions($fields);

        $totalUserNum = $this->getDataStatisticsService()->statisticsUserNum($conditions);
        $totalLearnCourseNum = $this->getDataStatisticsService()->statisticsOnlineCourseLearnNum($conditions);

        $avg = !empty($totalUserNum) ? round($totalLearnCourseNum / $totalUserNum, 1) : 0;

        return $this->createJsonResponse(array('data' => $avg));
    }

    public function userProjectPlansJoinCountAction(Request $request)
    {
        $fields = $request->request->all();
        if (!empty($fields['dataSearchTime'])) {
            $dateSearchTime = explode('-', $fields['dataSearchTime']);
            $fields['startDateTime'] = strtotime($dateSearchTime[0]);
            $fields['endDateTime'] = strtotime($dateSearchTime[1].' 23:59:59');
        }
        $conditions = $this->prepareSearchConditions($fields);

        $totalUserNum = $this->getDataStatisticsService()->statisticsUserNum($conditions);
        $totalJoinProjectNum = $this->getDataStatisticsService()->statisticsProjectPlanJoinNum($conditions);

        $avg = !empty($totalUserNum) ? round($totalJoinProjectNum / $totalUserNum, 1) : 0;

        return $this->createJsonResponse(array('data' => $avg));
    }

    public function userAttendOfflineActivitiesCountAction(Request $request)
    {
        $fields = $request->request->all();
        if (!empty($fields['dataSearchTime'])) {
            $dateSearchTime = explode('-', $fields['dataSearchTime']);
            $fields['startDateTime'] = strtotime($dateSearchTime[0]);
            $fields['endDateTime'] = strtotime($dateSearchTime[1].' 23:59:59');
        }
        $conditions = $this->prepareSearchConditions($fields);

        $totalUserNum = $this->getDataStatisticsService()->statisticsUserNum($conditions);
        $totalJoinNum = $this->getDataStatisticsService()->statisticsOfflineActivityJoinNum($conditions);

        $avg = !empty($totalUserNum) ? round($totalJoinNum / $totalUserNum, 1) : 0;

        return $this->createJsonResponse(array('data' => $avg));
    }

    public function userAttendExamsCountAction(Request $request)
    {
        if (!$this->isPluginInstalled('Exam')) {
            return $this->createJsonResponse(array('data' => '--'));
        }

        $fields = $request->request->all();
        if (!empty($fields['dataSearchTime'])) {
            $dateSearchTime = explode('-', $fields['dataSearchTime']);
            $fields['startDateTime'] = strtotime($dateSearchTime[0]);
            $fields['endDateTime'] = strtotime($dateSearchTime[1].' 23:59:59');
        }
        $conditions = $this->prepareSearchConditions($fields);

        $totalUserNum = $this->getDataStatisticsService()->statisticsUserNum($conditions);
        $totalJoinNum = $this->getExamService()->statisticsExamJoinNum($conditions);

        $avg = !empty($totalUserNum) ? round($totalJoinNum / $totalUserNum, 1) : 0;

        return $this->createJsonResponse(array('data' => $avg));
    }

    public function userAttendSurveysCountAction(Request $request)
    {
        if (!$this->isPluginInstalled('Survey')) {
            return $this->createJsonResponse(array('data' => '--'));
        }

        $fields = $request->request->all();
        if (!empty($fields['dataSearchTime'])) {
            $dateSearchTime = explode('-', $fields['dataSearchTime']);
            $fields['startDateTime'] = strtotime($dateSearchTime[0]);
            $fields['endDateTime'] = strtotime($dateSearchTime[1].' 23:59:59');
        }
        $conditions = $this->prepareSearchConditions($fields);

        $totalUserNum = $this->getDataStatisticsService()->statisticsUserNum($conditions);
        $totalJoinNum = $this->getSurveyService()->statisticsSurveyJoinNum($conditions);

        $avg = !empty($totalUserNum) ? round($totalJoinNum / $totalUserNum, 1) : 0;

        return $this->createJsonResponse(array('data' => $avg));
    }

    public function hotKeyWordAction(Request $request)
    {
        $fields = $request->request->all();
        if (!empty($fields['dataSearchTime'])) {
            $dateSearchTime = explode('-', $fields['dataSearchTime']);
            $fields['startDateTime'] = strtotime($dateSearchTime[0]);
            $fields['endDateTime'] = strtotime($dateSearchTime[1].' 23:59:59');
        }
        $conditions = $this->prepareSearchConditions($fields);
        $hotOnlineCourseStatistics = $this->getDataStatisticsService()->statisticsHotOnlineCourseCategoryIdsAndJoinNum($conditions, 9);
        $hotProjectPlanStatistics = $this->getDataStatisticsService()->statisticsHotProjectPlanCategoryIdsAndJoinNum($conditions, 9);
        $hotOfflineActivityStatistics = $this->getDataStatisticsService()->statisticsHotOfflineActivityCategoryIdsAndJoinNum($conditions, 9);

        $hotKeyWords = $this->buildHotKeyWord($hotOnlineCourseStatistics, $hotProjectPlanStatistics, $hotOfflineActivityStatistics);

        return $this->render(
            'CorporateTrainingBundle::admin/data/user-learn/hotKeyWord.html.twig',
            array(
                'hotKeyWords' => $hotKeyWords,
            )
        );
    }

    protected function buildHotKeyWord($hotOnlineCourseStatistics, $hotProjectPlanStatistics, $hotOfflineActivityStatistics)
    {
        $hotStatisticsArray = array(
            'admin.data_center.user_data.hot_keyword.online_course' => $hotOnlineCourseStatistics,
            'admin.data_center.user_data.hot_keyword.project_plan' => $hotProjectPlanStatistics,
            'admin.data_center.user_data.hot_keyword.offline_activity' => $hotOfflineActivityStatistics, );
        $maxCategoryNum = max(count($hotOnlineCourseStatistics), count($hotProjectPlanStatistics), count($hotOfflineActivityStatistics));

        $resultHotKeyWords = array();
        for ($index = 0; $index <= $maxCategoryNum; ++$index) {
            foreach ($hotStatisticsArray as $key => $hotKeyWord) {
                if (isset($hotKeyWord[$index])) {
                    $category = $this->getCategoryService()->getCategory($hotKeyWord[$index]['categoryId']);
                    $hotKeyWordResult = array(
                        'type' => $key,
                        'categoryName' => $category['name'],
                        'num' => $hotKeyWord[$index]['totalJoinNum'],
                        'name' => $this->trans($key),
                    );
                    array_push($resultHotKeyWords, $hotKeyWordResult);
                }
            }
            if (count($resultHotKeyWords) >= 9) {
                break;
            }
        }

        return array_slice($resultHotKeyWords, 0, 9);
    }

    public function userCountAction(Request $request)
    {
        $fields = $request->request->all();
        $conditions = $this->prepareSearchConditions($fields);
        $totalUserNum = $this->getDataStatisticsService()->statisticsUserNum($conditions);

        return $this->createJsonResponse(array('data' => $totalUserNum));
    }

    public function topTwentyListAction(Request $request)
    {
        $fields = $request->query->all();
        $fields['type'] = isset($fields['type']) ? $fields['type'] : 'week';
        $fields['courseType'] = isset($fields['courseType']) ? $fields['courseType'] : 'online';
        switch ($fields['type']) {
            case 'week':
                list($startDateTime, $endDateTime) = DateToolkit::generateStartDateAndEndDate('week');
                break;
            case 'month':
                list($startDateTime, $endDateTime) = DateToolkit::generateStartDateAndEndDate('month');
                break;
            case 'year':
                list($startDateTime, $endDateTime) = DateToolkit::generateStartDateAndEndDate('year');
        }

        $fields['startDateTime'] = strtotime($startDateTime);
        $fields['endDateTime'] = strtotime($endDateTime.' 23:59:59');

        $conditions = $this->prepareSearchConditions($fields);

        if ('online' == $fields['courseType']) {
            $ranks = $this->getDataStatisticsService()->statisticsPersonOnlineLearnTimeRankingList($conditions, 0, 20);
        } else {
            $ranks = $this->getDataStatisticsService()->statisticsPersonOfflineLearnTimeRankingList($conditions, 0, 20);
        }

        return $this->render('admin/data/user-learn/study-rank.html.twig', array(
            'studyRanks' => $ranks,
        ));
    }

    protected function buildTableTips()
    {
        return array(
            'online_course_learn' => $this->trans('admin.data_center.user_data.online_course.tips'),
            'project_plan' => $this->trans('admin.data_center.user_data.online_course.tips'),
            'offline_study_hours' => $this->trans('admin.data_center.user_data.offline_course.tips'),
            'online_study_hours' => $this->trans('admin.data_center.user_data.online_course_learn_hour.tips'),
            'offline_activity' => $this->trans('admin.data_center.user_data.activity.tips'),
            'subject_exam' => $this->trans('admin.data_center.user_data.exam.tips'),
            'survey' => $this->trans('admin.data_center.user_data.survey.tips'),
        );
    }

    protected function sumDateHourData($dateHourData, $webDateHourData)
    {
        if (!empty($dateHourData)) {
            foreach ($dateHourData as $dateHour => $date) {
                if (isset($webDateHourData[$dateHour])) {
                    $dateHourData[$dateHour]['count'] += $webDateHourData[$dateHour]['count'];
                }
            }

            if (!empty($webDateHourData)) {
                foreach ($webDateHourData as $webDateHour => $webData) {
                    if (!isset($dateHourData[$webDateHour])) {
                        $dateHourData[$webDateHour] = $webData;
                    }
                }
            }
        } else {
            $dateHourData = $webDateHourData;
        }

        return $dateHourData;
    }

    protected function getCustomColumnsDisplayData($userLearnModules)
    {
        $customColumnsDisplayData = array();

        foreach ($userLearnModules as $key => $module) {
            $displayData = array(
                'id' => $key,
                'name' => $this->trans($module['displayKey']),
                'checked' => false,
            );
            $customColumnsDisplayData[] = $displayData;
        }

        return $customColumnsDisplayData;
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
            $callable = array($this->createService($module['service']), $module['method']);
            $learnModulesData[$key] = call_user_func($callable, array('userIds' => $userIds, 'date' => $date));
        }

        $userLearnData = array();
        foreach ($userIds as $userId) {
            $user = $users[$userId];
            $userProfile = $userProfiles[$userId];
            $userLearnData[$userId]['truename'] = empty($userProfile['truename']) ? $user['nickname'] : $userProfile['truename'];
            $userLearnData[$userId]['coverImgUri'] = $this->getCoverImgUri($user, 'middle');
            $userLearnData[$userId]['postName'] = empty($posts[$user['postId']]) ? '--' : $posts[$user['postId']]['name'];
            $userLearnData[$userId]['org'] = $this->getOrgDisplayData($user['orgIds'], $orgs);

            foreach ($learnModulesData as $module => $learnModuleData) {
                $userLearnData[$userId][$module] = isset($learnModuleData[$userId]) ? $learnModuleData[$userId] : 0;
            }
            $userLearnData[$userId]['study_record'] = $this->generateUrl('study_record_project_plan',
                array('userId' => $userId));
            $userLearnData[$userId]['user_info'] = $this->generateUrl('admin_user_show', array('id' => $userId));
        }

        return array_values($userLearnData);
    }

    protected function getOrgDisplayData($orgIds, $orgs)
    {
        $orgDisplayData = array();
        foreach ($orgIds as $orgId) {
            $orgDisplayData[] = array(
                'name' => $orgs[$orgId]['name'],
                'code' => $orgs[$orgId]['code'],
            );
        }

        return $orgDisplayData;
    }

    protected function getCoverImgUri($user, $type)
    {
        $coverPath = $this->get('web.twig.app_extension')->userAvatar($user, 'medium');

        return $this->getWebExtension()->getFpath($coverPath, 'avatar.png');
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

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('CorporateTrainingBundle:Course:CourseSetService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\Course\Service\MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->createService('CorporateTrainingBundle:Course:MemberService');
    }

    /**
     * @return DataStatisticsService
     */
    protected function getDataStatisticsService()
    {
        return $this->createService('CorporateTrainingBundle:DataStatistics:DataStatisticsService');
    }

    /**
     * @return MemberService
     */
    protected function getOfflineActivityMemberService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineActivity:MemberService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\MemberService
     */
    protected function getProjectPlanMemberService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:MemberService');
    }

    /**
     * @return ProjectPlanService
     */
    protected function getProjectPlanService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:ProjectPlanService');
    }

    /**
     * @return ExamService
     */
    protected function getExamService()
    {
        return $this->createService('ExamPlugin:Exam:ExamService');
    }

    /**
     * @return SurveyService
     */
    protected function getSurveyService()
    {
        return $this->createService('SurveyPlugin:Survey:SurveyService');
    }

    /**
     * @return CategoryService
     */
    protected function getCategoryService()
    {
        return $this->createService('CorporateTrainingBundle:Taxonomy:CategoryService');
    }

    /**
     * @return OfflineActivityService
     */
    protected function getOfflineActivityService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineActivity:OfflineActivityService');
    }

    /**
     * @return PostService
     */
    protected function getPostService()
    {
        return $this->createService('CorporateTrainingBundle:Post:PostService');
    }
}
