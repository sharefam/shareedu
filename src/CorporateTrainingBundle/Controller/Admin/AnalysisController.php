<?php

namespace CorporateTrainingBundle\Controller\Admin;

use AppBundle\Common\ArrayToolkit;
use Biz\CloudPlatform\CloudAPIFactory;
use CorporateTrainingBundle\Biz\Focus\Service\FocusService;
use CorporateTrainingBundle\Biz\ProjectPlan\Service\ProjectPlanService;
use Biz\Role\Util\PermissionBuilder;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\CurlToolkit;
use AppBundle\Controller\Admin\AnalysisController as BaseAnalysisController;
use Topxia\Service\Common\ServiceKernel;

class AnalysisController extends BaseAnalysisController
{
    private $homePageQuickEntranceCodes = array(
        'admin_train_teach_manage_my_teaching_courses',
        'admin_train_teach_manage_project_plan_teaching',
        'admin_train_exam_manage_list',
        'admin_project_plan_manage',
        'admin_role_manage',
        'admin_block',
        'admin_setting_theme',
        'admin_setting_mobile_settings',
    );

    public function indexAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();

        $quickEntrances = $this->getUserQuickEntrances();
        $userProfile = $this->getUserService()->getUserProfile($currentUser['id']);
        $homePageQuickEntranceCodes = $this->homePageQuickEntranceCodes;
        if ($userProfile['quick_entrance']) {
            $homePageQuickEntranceCodes = $userProfile['quick_entrance'];
        }

        $quickEntrances = $this->getHomePageQuickEntrances($quickEntrances, $homePageQuickEntranceCodes);

        return $this->render('admin/default/corporate-training-admin-index.html.twig', array(
            'quickEntrances' => $quickEntrances,
        ));
    }

    public function chooseQuickEntrancesAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();

        if ('POST' == $request->getMethod()) {
            $params = $request->request->all();
            $params['data'] = json_decode($params['data'], true);

            if (empty($params['data'])) {
                $params['data'] = array(-1);
            }
            $updatedUserProfile = $this->getUserService()->updateUserProfile($currentUser['id'], array('quick_entrance' => $params['data']));

            $quickEntrances = $this->getUserQuickEntrances();
            $quickEntrances = $this->getHomePageQuickEntrances($quickEntrances, $updatedUserProfile['quick_entrance']);

            return $this->render('admin/default/quick-entrance.html.twig', array(
                'quickEntrances' => $quickEntrances,
            ));
        }

        $userProfile = $this->getUserService()->getUserProfile($currentUser['id']);
        $quickEntrances = $this->getUserQuickEntrances();
        $homePageQuickEntranceCodes = $this->homePageQuickEntranceCodes;
        if ($userProfile['quick_entrance']) {
            $homePageQuickEntranceCodes = $userProfile['quick_entrance'];
        }

        foreach ($quickEntrances as &$item) {
            if (!empty($item['data'])) {
                foreach ($item['data'] as &$entrance) {
                    if (in_array($entrance['code'], $homePageQuickEntranceCodes)) {
                        $entrance['checked'] = true;
                    } else {
                        $entrance['checked'] = false;
                    }
                }
            }
        }

        return $this->render('admin/default/quick-entrance-model.html.twig', array(
            'quickEntrances' => $quickEntrances,
        ));
    }

    protected function getHomePageQuickEntrances($quickEntrances, $homePageQuickEntranceCodes)
    {
        foreach ($quickEntrances as &$item) {
            if (!empty($item['data'])) {
                $item['data'] = array_filter(
                    $item['data'],
                    function ($entrance) use ($homePageQuickEntranceCodes) {
                        return in_array($entrance['code'], $homePageQuickEntranceCodes, true);
                    }
                );
            }
        }

        return $quickEntrances;
    }

    protected function getUserQuickEntrances()
    {
        $currentUser = $this->getCurrentUser();
        $tree = PermissionBuilder::instance()->findAllPermissionTree(true);

        $allPermissions = $tree->toArray();
        $modules = $allPermissions['children'][0]['children'];

        $quickEntrances = array();
        foreach ($modules as $module) {
            $quickEntrances[$module['code']]['data'] = $this->getQuickEntrancesArray($module, array(), $currentUser);
            $quickEntrances[$module['code']]['name'] = $this->trans($module['name'], array(), 'menu');
        }

        return $quickEntrances;
    }

    private function getQuickEntrancesArray($module, $moduleQuickEntrances, $currentUser)
    {
        if (isset($module['quick_entrance']) && $module['quick_entrance'] && $currentUser->hasPermission($module['code'])) {
            $module['name'] = $this->trans($module['name'], array(), 'menu');
            $moduleQuickEntrances = array($module);
        } else {
            $moduleQuickEntrances = array();
        }

        if (isset($module['children'])) {
            foreach ($module['children'] as $child) {
                $moduleQuickEntrances = array_merge($moduleQuickEntrances, $this->getQuickEntrancesArray($child, $moduleQuickEntrances, $currentUser));
            }
        }

        return $moduleQuickEntrances;
    }

    public function systemStatusAction(Request $request)
    {
        list($mainAppUpgrade, $upgradeAppCount) = $this->checkCloudAppUpgradeInfo();
        list($android, $ios) = $this->getMobileAppVersion();
        $mobileSetting = $this->getSettingService()->get('mobile', array());
        $mailStatus = $this->isMailEnabled();
        $eduCloudStatus = $this->getEduCloudStatus();

        return $this->render('admin/default/corporate-training-system-status.html.twig', array(
            'upgradeAppCount' => $upgradeAppCount,
            'mainAppUpgrade' => $mainAppUpgrade,
            'android' => $android,
            'ios' => $ios,
            'mobileSetting' => $mobileSetting,
            'mailServiceStatus' => $mailStatus,
            'eduCloudStatus' => $eduCloudStatus,
        ));
    }

    public function systemDataOverviewAction()
    {
        $userData = $this->getUsersNumberData();
        $courseDate = $this->getCoursesData();
        $teacherData = $this->getTeachersData();
        $projectPlanData = $this->getProjectPlansData();

        return $this->render('admin/default/data-overview.html.twig', array(
            'userData' => $userData,
            'teacherData' => $teacherData,
            'courseData' => $courseDate,
            'projectPlanData' => $projectPlanData,
        ));
    }

    private function isMailEnabled()
    {
        $cloudEmail = $this->getSettingService()->get('cloud_email_crm', array());

        if (!empty($cloudEmail) && 'enable' === $cloudEmail['status']) {
            return true;
        }

        $mailer = $this->getSettingService()->get('mailer', array());

        if (!empty($mailer) && $mailer['enabled']) {
            return true;
        }

        return false;
    }

    private function getUsersNumberData()
    {
        $defaultConditions = array(
            'locked' => 0,
            'noType' => 'system',
        );
        $allUsersCount = $this->getUserService()->countUsers($defaultConditions);
        $defaultConditions['startTime'] = strtotime(date('Y').'-01-01 00:00:00');
        $newUsersCount = $this->getUserService()->countUsers($defaultConditions);

        return array('allUsersCount' => $allUsersCount, 'newUsersCount' => $newUsersCount);
    }

    private function getCoursesData()
    {
        $defaultConditions = array();
        $allCoursesCount = $this->getCourseSetService()->countCourseSets($defaultConditions);
        $defaultConditions['startTime'] = strtotime(date('Y').'-01-01 00:00:00');
        $newCoursesCount = $this->getCourseSetService()->countCourseSets($defaultConditions);

        return array('allCoursesCount' => $allCoursesCount, 'newCoursesCount' => $newCoursesCount);
    }

    private function getTeachersData()
    {
        $defaultConditions = array(
            'locked' => 0,
            'noType' => 'system',
            'roles' => 'ROLE_TEACHER',
        );
        $allTeachersCount = $this->getUserService()->countUsers($defaultConditions);
        $defaultConditions['startTime'] = strtotime(date('Y').'-01-01 00:00:00');
        $newTeachersCount = $this->getUserService()->countUsers($defaultConditions);

        return array('allTeachersCount' => $allTeachersCount, 'newTeachersCount' => $newTeachersCount);
    }

    private function getProjectPlansData()
    {
        $defaultConditions = array();
        $allProjectPlansCount = $this->getProjectPlanService()->countProjectPlans($defaultConditions);
        $defaultConditions['createdTime_GE'] = strtotime(date('Y').'-01-01 00:00:00');
        $newProjectPlansCount = $this->getProjectPlanService()->countProjectPlans($defaultConditions);

        return array('allProjectPlansCount' => $allProjectPlansCount, 'newProjectPlansCount' => $newProjectPlansCount);
    }

    private function checkCloudAppUpgradeInfo()
    {
        $apps = $this->getAppService()->checkAppUpgrades();
        $upgradeAppCount = count($apps);
        if (!is_null($apps)) {
            $indexApps = ArrayToolkit::index($apps, 'code');
        }
        $mainAppUpgrade = empty($indexApps['TRAININGMAIN']) ? array() : $indexApps['TRAININGMAIN'];

        if ($mainAppUpgrade) {
            $upgradeAppCount = $upgradeAppCount - 1;
        }

        return array($mainAppUpgrade, $upgradeAppCount);
    }

    private function getMobileAppVersion()
    {
        return array($this->getMobileVersion('android'), $this->getMobileVersion('iphone'));
    }

    private function getEduCloudStatus()
    {
        try {
            $api = CloudAPIFactory::create('root');
            $overview = $api->get("/cloud/{$api->getAccessKey()}/overview");
            if (!$overview['enabled']) {
                return false;
            }

            if (!($overview['accessCloud'])) {
                return false;
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function userStatisticsAction(Request $request)
    {
        $userCount = $this->getUserService()->countUsers(array('noType' => 'system', 'locked' => 0));

        $conditions = array(
            'loginStartTime' => strtotime(date('Y-m-d')),
            'loginEndTime' => strtotime(date('Y-m-d')) + 86400,
        );

        $loginCount = $this->getUserService()->countUsers($conditions);

        $conditions = array(
            'updatedTime_GE' => strtotime(date('Y-m-d')),
            'finishedTime_LT' => strtotime(date('Y-m-d')) + 86400,
        );

        $todayLearned = $this->getTaskResultService()->searchTaskResults($conditions, array('finishedTime' => 'desc'), '0', PHP_INT_MAX);

        $users = ArrayToolkit::column($todayLearned, 'userId');
        $taskNum = count(array_unique($users));

        $userCountInfo = $this->getUserService()->countUsersByLockedStatus();

        return $this->render('default/user-statistics.html.twig', array(
            'userCount' => $userCount,
            'userCountInfo' => $userCountInfo,
            'loginCount' => $loginCount,
            'learnedCount' => $taskNum,
        ));
    }

    public function userActiveChartAction(Request $request)
    {
        $data = array();
        $count = 0;

        $condition = $request->request->all();
        $endTime = strtotime($condition['endTime']);
        $startTime = strtotime('+1 year', strtotime($condition['startTime']));
        if ($endTime > $startTime) {
            $condition['endTime'] = date('Y-m-d', $startTime);
        }
        $condition['analysisDateType'] = 'login';
        $timeRange = $this->getTimeRange($condition);

        if (!$timeRange) {
            $this->setFlashMessage('danger', 'admin.analysis.message.date_error');

            return $this->redirect($this->generateUrl('admin'));
        }

        $loginData = $this->getLogService()->analysisLoginDataByTime($timeRange['startTime'], $timeRange['endTime']);

        $data = $this->fillAnalysisData($condition, $loginData);
        foreach ($loginData as $key => $value) {
            $count += $value['count'];
        }

        $loginStartData = $this->getLogService()->searchLogs(array('action' => 'login_success'), 'createdByAsc', 0, 1);

        if ($loginStartData) {
            $loginStartDate = date('Y-m-d', $loginStartData[0]['createdTime']);
        }

        $dataInfo = $this->getDataInfo($condition, $timeRange);

        return $this->render('default/user-active-chart.html.twig', array(
            'data' => $data,
            'count' => $count,
            'loginStartDate' => empty($loginStartDate) ? array() : $loginStartDate,
            'dataInfo' => $dataInfo,
        ));
    }

    public function validateSyncOrgAction(Request $request)
    {
        $inspectList = array(
            $this->addInspectRole('host', $this->inspectSyncOrg($request)),
        );
        $inspectList = array_filter($inspectList);

        return $this->render('admin/default/domain.html.twig', array(
            'inspectList' => $inspectList,
        ));
    }

    private function addInspectRole($name, $value)
    {
        if (empty($value) || 'ok' == $value['status']) {
            return array();
        }

        return array('name' => $name, 'value' => $value);
    }

    private function inspectSyncOrg($request)
    {
        $sync = $this->getSettingService()->get('sync_department_setting', array());
        $siteSetting = $this->getSettingService()->get('site');

        if ($sync && $sync['enable'] && $sync['times'] <= 0) {
            return array(
                'status' => 'warning',
                'errorMessage' => ServiceKernel::instance()->trans('admin.analysis.message.inspect_sync_org'),
                'except' => $siteSetting['url'],
                'actually' => $request->server->get('HTTP_HOST'),
                'settingUrl' => $this->generateUrl('admin_org'),
            );
        }

        return array();
    }

    protected function getDataInfo($condition, $timeRange)
    {
        $startTime = strtotime(date('Y-m', $timeRange['startTime']).'-01 00:00:01');
        $endTime = strtotime(date('Y-m', $timeRange['endTime']).'-01 00:00:01');

        return array(
            'startTime' => date('Y-m-d', $timeRange['startTime']),
            'endTime' => date('Y-m-d', $timeRange['endTime'] - 24 * 3600),
            'currentMonthStart' => date('Y-m-d', strtotime(date('Y-m', time()))),
            'currentMonthEnd' => date('Y-m-d', strtotime(date('Y-m-d', time()))),
            'lastMonthStart' => date('Y-m-d', strtotime(date('Y-m', strtotime('-1 month', $startTime)))),
            'lastMonthEnd' => date('Y-m-d', strtotime(date('Y-m', $endTime)) - 24 * 3600),
            'lastThreeMonthsStart' => date('Y-m-d', strtotime(date('Y-m', strtotime('-2 month', $startTime)))),
            'lastThreeMonthsEnd' => date('Y-m-d', strtotime(date('Y-m-d', time()))),
            'analysisDateType' => $condition['analysisDateType'], );
    }

    private function getMobileVersion($system)
    {
        $mobileVersion = CurlToolkit::request('GET', "http://www.edusoho.com/version/edusoho-training-{$system}", array());
        if (empty($mobileVersion)) {
            return;
        }

        return $mobileVersion;
    }

    /**
     * @return FocusService
     */
    protected function getFocusService()
    {
        return $this->createService('CorporateTrainingBundle:Focus:FocusService');
    }

    protected function getAppService()
    {
        return $this->createService('CloudPlatform:AppService');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return ProjectPlanService
     */
    protected function getProjectPlanService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:ProjectPlanService');
    }
}
