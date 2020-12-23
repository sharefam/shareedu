<?php

namespace CorporateTrainingBundle\Twig\Extension;

use AppBundle\Common\ArrayToolkit;
use Codeages\Biz\Framework\Context\Biz;
use CorporateTrainingBundle\Biz\Classroom\Service\ClassroomService;
use CorporateTrainingBundle\Biz\Course\Service\CourseService;
use CorporateTrainingBundle\Biz\ManagePermission\Service\ManagePermissionOrgService;
use CorporateTrainingBundle\Biz\Org\Service\Impl\OrgServiceImpl;
use CorporateTrainingBundle\Biz\ResourceScope\Service\ResourceVisibleScopeService;
use CorporateTrainingBundle\Biz\UserGroup\Service\UserGroupService;
use CorporateTrainingBundle\Biz\ProjectPlan\Service\ProjectPlanService;
use CorporateTrainingBundle\Common\OrgTreeToolkit;
use Topxia\Service\Common\ServiceKernel;

class WebExtension extends \Twig_Extension
{
    protected $container;

    protected $biz;

    public function __construct($container, Biz $biz)
    {
        $this->container = $container;
        $this->biz = $biz;
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('timeFormatBySecond', array($this, 'timeFormatReturnBySecondFilter')),
            new \Twig_SimpleFilter('time_format', array($this, 'timeFormatReturnHour')),
            new \Twig_SimpleFilter('time_format_hour', array($this, 'timeFormatHour')),
            new \Twig_SimpleFilter('time_judgment', array($this, 'timeJudgmentFilter')),
            new \Twig_SimpleFilter('weekday', array($this, 'timeFormatReturnWeekday')),
            new \Twig_SimpleFilter('month_format', array($this, 'monthFormat')),
            new \Twig_SimpleFilter('highlight_keyword', array($this, 'searchKeywordFilter')),
            new \Twig_SimpleFilter('num_format', array($this, 'numFormat')),
        );
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('all_post_choices', array($this, 'allPostChoices')),
            new \Twig_SimpleFunction('build_org_tree', array($this, 'buildOrgTreeByCodes')),
            new \Twig_SimpleFunction('avg_learn_time', array($this, 'avgLearnTime')),
            new \Twig_SimpleFunction('is_ding_talk', array($this, 'isDingTalk')),
            new \Twig_SimpleFunction('can_manage_course', array($this, 'canManageCourse')),
            new \Twig_SimpleFunction('is_user_project_plan_course', array($this, 'isUserProjectPlanCourse')),
            new \Twig_SimpleFunction('is_same_day', array($this, 'isSameDay')),
            new \Twig_SimpleFunction('underline_to_line', array($this, 'underlineToLine')),
            new \Twig_SimpleFunction('can_operate_offline_exam', array($this, 'canOperateOfflineExam')),
            new \Twig_SimpleFunction('get_parameter', array($this, 'getParameter')),
            new \Twig_SimpleFunction('get_post_tree', array($this, 'getPostTree')),
            new \Twig_SimpleFunction('get_user_group_tree', array($this, 'getUserGroupsTree')),
            new \Twig_SimpleFunction('get_visible_range', array($this, 'getVisibleRange')),
            new \Twig_SimpleFunction('get_access_range', array($this, 'getAccessRange')),
            new \Twig_SimpleFunction('set_manage_permission_org_tree', array($this, 'getManagePermissionOrgTree')),
            new \Twig_SimpleFunction('can_manage_project_plan', array($this, 'canManageProjectPlan')),
            new \Twig_SimpleFunction('can_manage_resource_org_scope', array($this, 'canManageResourceOrgScope')),
            new \Twig_SimpleFunction('get_resource_visible_scopes', array($this, 'getResourceVisibleScopes')),
            new \Twig_SimpleFunction('get_resource_access_scopes', array($this, 'getResourceAccessScopes')),
            new \Twig_SimpleFunction('can_manage_classroom', array($this, 'canManageClassroom')),
        );
    }

    public function underlineToLine($name)
    {
        $name = strtolower($name);
        $name = str_replace('_', '-', $name);

        return 'zh-cn' == $name ? '' : $name;
    }

    public function timeFormatReturnWeekday($time)
    {
        if (is_numeric($time)) {
            $weekday = array('day.format.Sun', 'day.format.Mon', 'day.format.Tues', 'day.format.Wed', 'day.format.Thu', 'day.format.Fri', 'day.format.Sat');

            return $weekday[date('w', $time)];
        }

        return false;
    }

    public function numFormat($num)
    {
        if (is_numeric($num)) {
            return sprintf('%.1f', $num);
        }

        return false;
    }

    public function monthFormat($month)
    {
        $key = 'month.format.unknow';
        switch ($month) {
            case 1:
                $key = 'month.format.jan';
                break;
            case 2:
                $key = 'month.format.feb';
                break;
            case 3:
                $key = 'month.format.mar';
                break;
            case 4:
                $key = 'month.format.apr';
                break;
            case 5:
                $key = 'month.format.may';
                break;
            case 6:
                $key = 'month.format.jun';
                break;
            case 7:
                $key = 'month.format.jul';
                break;
            case 8:
                $key = 'month.format.aug';
                break;
            case 9:
                $key = 'month.format.sep';
                break;
            case 10:
                $key = 'month.format.oct';
                break;
            case 11:
                $key = 'month.format.nov';
                break;
            case 12:
                $key = 'month.format.dec';
                break;
        }

        return $this->trans($key);
    }

    public function isSameDay($day, $otherDay)
    {
        $day = date('Y-m-d', $day);
        $otherDay = date('Y-m-d', $otherDay);
        $day = getdate(strtotime($day));
        $otherDay = getdate(strtotime($otherDay));

        if (($day['year'] === $otherDay['year']) && ($day['yday'] === $otherDay['yday'])) {
            return true;
        } else {
            return false;
        }
    }

    public function timeJudgmentFilter($time)
    {
        if (empty($time)) {
            return;
        }

        $hour = date('H', $time);
        if ($hour >= 0 && $hour < 12) {
            return $this->trans('site.twig.extension.morning_greeting');
        }

        if ($hour >= 12 && $hour < 18) {
            return $this->trans('site.twig.extension.afternoon_greeting');
        }

        if ($hour >= 18 && $hour < 24) {
            return $this->trans('site.twig.extension.night_greeting');
        }
    }

    public function timeFormatReturnHour($learnTime)
    {
        return substr(sprintf('%.2f', $learnTime / 3600), 0, -1);
    }

    public function timeFormatHour($learnTime)
    {
        return substr(sprintf('%.2f', $learnTime / 3600), 0, -1).' '.ServiceKernel::instance()->trans('site.date.hour');
    }

    public function avgLearnTime($totalLearnTime, $userCount)
    {
        if (empty($totalLearnTime) || empty($userCount)) {
            return 0;
        }

        return floor($totalLearnTime / $userCount);
    }

    public function humpToLine($str)
    {
        $str = preg_replace_callback('/([A-Z]{1})/', function ($matches) {
            return '-'.strtolower($matches[0]);
        }, $str);

        return $str;
    }

    public function canManageCourse($courseId)
    {
        return $this->getCourseService()->canManageCourse($courseId);
    }

    public function canManageProjectPlan($projectPlanId)
    {
        return $this->getProjectPlanService()->canManageProjectPlan($projectPlanId);
    }

    public function searchKeywordFilter($content, $keyword)
    {
        if (empty($keyword)) {
            return $content;
        }

        $text = str_replace($keyword, "<span style='color:#ff0000'>".$keyword.'</span>', $content);
        echo $text;
    }

    public function allPostChoices()
    {
        $allPosts = $this->getPostService()->getAllPosts();
        $choices = array();

        foreach ($allPosts as $post) {
            $choices[$post['id']] = $post['name'];
        }

        return $choices;
    }

    /*
    * 查询返回秒钟显示格式处理
    */
    public function timeFormatReturnBySecondFilter($learnTime)
    {
        $hour = floor($learnTime / 3600);
        $minute = floor(($learnTime % 3600) / 60);
        if (0 == $hour && 0 != $minute) {
            $time = $minute.ServiceKernel::instance()->trans('site.date.minute');
        } elseif (0 == $minute && 0 == $hour) {
            $time = '0';
        } elseif (0 == $minute && 0 != $hour) {
            $time = $hour.ServiceKernel::instance()->trans('site.date.hour');
        } else {
            $time = $hour.ServiceKernel::instance()->trans('site.date.hour').$minute.ServiceKernel::instance()->trans('site.date.minute');
        }

        return $time;
    }

    public function buildOrgTreeByCodes($orgCodes)
    {
        if (empty($orgCodes)) {
            return json_encode(array());
        }

        $tree = $this->getOrgService()->buildVisibleOrgTreeByOrgCodes($orgCodes);

        return json_encode($tree);
    }

    public function isDingTalk()
    {
        return false !== strpos($this->container->get('request')->headers->get('User-Agent'), 'DingTalk');
    }

    public function isUserProjectPlanCourse($courseId, $userId)
    {
        $user = $this->getUserService()->getUser($userId);
        if (empty($user)) {
            return false;
        }

        return $this->getCourseService()->canUserAutoJoinCourse($user, $courseId);
    }

    public function canOperateOfflineExam($user, $exam)
    {
        if (in_array('ROLE_SUPER_ADMIN', $user['roles'])) {
            return true;
        }

        if (in_array('ROLE_TRAINING_ADMIN', $user['roles'])) {
            return true;
        }

        $currentUser = ServiceKernel::instance()->getCurrentUser();
        if ($currentUser->hasPermission('admin_project_plan')) {
            return true;
        }

        return false;
    }

    public function getParameter($parameter)
    {
        if ($this->container->hasParameter($parameter)) {
            return $this->container->getParameter($parameter);
        }

        return false;
    }

    public function getManagePermissionOrgTree($settingOrgIds)
    {
        $tree = array_values(OrgTreeToolkit::makeTree($this->getOrgService()->getPermissionOrgTreeData($settingOrgIds, true)));

        return json_encode($tree);
    }

    public function getPostTree()
    {
        $postTree = $this->getPostService()->getPostTree();

        return json_encode($postTree);
    }

    public function getUserGroupsTree()
    {
        $tree = array();
        $userGroups = $this->getUserGroupService()->findAllUserGroups();

        foreach ($userGroups as $group) {
            $tree[] = array('id' => $group['id'], 'name' => $group['name'], 'selectable' => true, 'nodes' => array());
        }

        return json_encode($tree);
    }

    public function getVisibleRange($resourceType, $resourceId, $type)
    {
        $strategy = $this->getStrategyContext()->createStrategy($type);
        $scopes = $strategy->findVisibleScopesByResourceTypeAndResourceId($resourceType, $resourceId);
        $scopes = ArrayToolkit::column($scopes, 'scope');

        if ('Org' == $type && !empty($scopes)) {
            $orgs = $this->getOrgService()->findOrgsByIds($scopes);
            $scopes = $this->getOrgService()->findOrgsByPrefixOrgCodes(ArrayToolkit::column($orgs, 'orgCode'), array('id'));
            $scopes = ArrayToolkit::column($scopes, 'id');
        }

        return !empty($scopes) ? implode(',', $scopes) : '';
    }

    public function getAccessRange($resourceType, $resourceId, $type)
    {
        $strategy = $this->getStrategyContext()->createStrategy($type);
        $scopes = $strategy->findAccessScopesByResourceTypeAndResourceId($resourceType, $resourceId);
        $scopes = ArrayToolkit::column($scopes, 'scope');

        if ('Org' == $type && !empty($scopes)) {
            $orgs = $this->getOrgService()->findOrgsByIds($scopes);
            $scopes = $this->getOrgService()->findOrgsByPrefixOrgCodes(ArrayToolkit::column($orgs, 'orgCode'), array('id'));
            $scopes = ArrayToolkit::column($scopes, 'id');
        }
        if ('HireDate' == $type) {
            return array_shift($scopes);
        }

        return !empty($scopes) ? implode(',', $scopes) : '';
    }

    public function getResourceVisibleScopes($resourceType, $resourceId, $type)
    {
        $strategy = $this->getStrategyContext()->createStrategy($type);
        $scopes = $strategy->findVisibleScopesByResourceTypeAndResourceId($resourceType, $resourceId);

        return ArrayToolkit::column($scopes, 'scope');
    }

    public function getResourceAccessScopes($resourceType, $resourceId, $type)
    {
        $strategy = $this->getStrategyContext()->createStrategy($type);
        $scopes = $strategy->findAccessScopesByResourceTypeAndResourceId($resourceType, $resourceId);

        return ArrayToolkit::column($scopes, 'scope');
    }

    public function canManageResourceOrgScope($resourceId, $resourceType, $type = 'visible')
    {
        if ('visible' == $type) {
            $orgVisibleScopes = $this->getResourceVisibleScopeOrgDao()->findByResourceTypeAndResourceId($resourceType, $resourceId);
        } else {
            $orgVisibleScopes = $this->getResourceAccessScopeOrgDao()->findByResourceTypeAndResourceId($resourceType, $resourceId);
        }

        $oldSettingOrgIds = ArrayToolkit::column($orgVisibleScopes, 'scope');
        $userManageIds = $this->biz['user']->getManageOrgIds();
        if (empty($oldSettingOrgIds)) {
            return true;
        }
        if ($this->getManagePermissionService()->checkOrgManagePermission($userManageIds, $oldSettingOrgIds)) {
            return true;
        }

        return false;
    }

    public function canManageClassroom($classroomId)
    {
        return $this->getClassroomService()->canManageClassroom($classroomId);
    }

    protected function getResourceVisibleScopeOrgDao()
    {
        return $this->biz->Dao('ResourceScope:ResourceVisibleScopeOrgDao');
    }

    protected function getResourceAccessScopeOrgDao()
    {
        return $this->biz->Dao('ResourceScope:ResourceAccessScopeOrgDao');
    }

    /**
     * @return ResourceVisibleScopeService
     */
    protected function getResourceVisibleScopeService()
    {
        return $this->createService('CorporateTrainingBundle:ResourceScope:ResourceVisibleScopeService');
    }

    /**
     * @return UserGroupService
     */
    protected function getUserGroupService()
    {
        return $this->createService('CorporateTrainingBundle:UserGroup:UserGroupService');
    }

    /**
     * @return ManagePermissionOrgService
     */
    protected function getManagePermissionService()
    {
        return $this->createService('CorporateTrainingBundle:ManagePermission:ManagePermissionOrgService');
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    protected function getPostService()
    {
        return $this->createService('CorporateTrainingBundle:Post:PostService');
    }

    protected function getPostCourseService()
    {
        return $this->createService('PostCourse:PostCourseService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return OrgServiceImpl
     */
    protected function getOrgService()
    {
        return $this->createService('Org:OrgService');
    }

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

    protected function getUserPostCourseService()
    {
        return $this->createService('CorporateTrainingBundle:PostCourse:UserPostCourseService');
    }

    protected function createService($alias)
    {
        return $this->biz->service($alias);
    }

    private function trans($key, $parameters = array())
    {
        return $this->container->get('translator')->trans($key, $parameters);
    }

    protected function getStrategyContext()
    {
        return $this->biz['resource_scope_strategy_context'];
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->biz->service('CorporateTrainingBundle:Classroom:ClassroomService');
    }
}
