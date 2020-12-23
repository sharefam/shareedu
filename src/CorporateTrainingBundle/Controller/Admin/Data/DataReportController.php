<?php

namespace CorporateTrainingBundle\Controller\Admin\Data;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\Admin\BaseController;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;
use CorporateTrainingBundle\Biz\DataStatistics\Service\UserDailyLearnRecordService;
use CorporateTrainingBundle\Biz\OfflineActivity\Service\CategoryService;
use CorporateTrainingBundle\Biz\OfflineActivity\Service\OfflineActivityService;
use CorporateTrainingBundle\Biz\Org\Service\OrgService;
use CorporateTrainingBundle\Biz\Post\Service\PostService;
use CorporateTrainingBundle\Biz\PostCourse\Service\PostCourseService;
use CorporateTrainingBundle\Biz\User\Service\UserOrgService;
use CorporateTrainingBundle\Biz\User\Service\UserService;
use CorporateTrainingBundle\Common\DateToolkit;
use Symfony\Component\HttpFoundation\Request;

class DataReportController extends BaseController
{
    public function courseLearnDataStatisticDepartmentAction(Request $request)
    {
        $renderData = $this->buildCourseLearnStatisticDepartmentData($request);

        return $this->render(
            'admin/data/course/data-report/course-learn-data-department.html.twig',
            $renderData
        );
    }

    public function courseLearnDataStatisticDepartmentListAction(Request $request)
    {
        $renderData = $this->buildCourseLearnStatisticDepartmentData($request, 'ajax');

        return $this->render(
            'admin/data/course/data-report/course-learn-data-department-list.html.twig',
            $renderData
        );
    }

    public function courseLearnDataStatisticPostAction(Request $request)
    {
        $renderData = $this->buildCourseLearnStatisticPostData($request);

        return $this->render(
            'admin/data/course/data-report/course-learn-data-post.html.twig',
            $renderData
        );
    }

    public function courseLearnDataStatisticPostListAction(Request $request)
    {
        $renderData = $this->buildCourseLearnStatisticPostData($request, 'ajax');

        return $this->render(
            'admin/data/course/data-report/course-learn-data-post-list.html.twig',
            $renderData
        );
    }

    public function courseLearnDataStatisticCategoryAction(Request $request)
    {
        $renderData = $this->buildCourseLearnStatisticCategoryData($request);

        return $this->render(
            'admin/data/course/data-report/course-learn-data-category.html.twig',
            $renderData
        );
    }

    public function courseLearnDataStatisticCategoryListAction(Request $request)
    {
        $renderData = $this->buildCourseLearnStatisticCategoryData($request, 'ajax');

        return $this->render(
            'admin/data/course/data-report/course-learn-data-category-list.html.twig',
            $renderData
        );
    }

    public function ajaxGetLearnAndLoginStatisticDataAction(Request $request)
    {
        $cursor = $request->query->get('cursor', 0);
        $type = $request->query->get('type', 0);
        list($startDate, $endDate) = DateToolkit::generateStartDateAndEndDate($type, $cursor, 'date');

        $conditions = array(
            'startDateTime' => $startDate,
            'endDateTime' => $endDate,
        );

        if (!empty($conditions['startDateTime'])) {
            $conditions['startDateTime'] = strtotime($conditions['startDateTime']);
        }

        if (!empty($conditions['endDateTime'])) {
            $conditions['endDateTime'] = strtotime($conditions['endDateTime']);
        }
        $learnUsersNum = $this->getDataStatisticsService()->statisticsLearnUsersNumGroupByDate($conditions);
        $learnTime = $this->getDataStatisticsService()->statisticsTotalLearnTimeGroupByDate($conditions);
        $loginData = $this->getLogService()->analysisLoginDataByTime(strtotime($startDate), strtotime($endDate));

        $learnUsersNum = ArrayToolkit::index($learnUsersNum, 'date');
        $learnTime = ArrayToolkit::index($learnTime, 'date');
        foreach ($loginData as &$value) {
            $value['date'] = strtotime($value['date']);
        }
        $loginData = ArrayToolkit::index($loginData, 'date');
        $learnUsersNum = $this->buildAjaxStatisticData($type, $cursor, $learnUsersNum, 'learnedUserNum');
        $learnTime = $this->buildAjaxStatisticData($type, $cursor, $learnTime, 'totalLearnTime');
        $loginData = $this->buildAjaxStatisticData($type, $cursor, $loginData, 'count');
        foreach ($learnTime['data'] as &$datum) {
            $datum = DateToolkit::convertSecondToHour($datum);
        }

        $chartData = array(
            'xAxis' => array('data' => $learnUsersNum['date']),
            'series' => array(
                'dataLearnTime' => $learnTime['data'],
                'dataLearnUsersNum' => $learnUsersNum['data'],
                'dataLoginNum' => $loginData['data'],
            ),
        );

        return $this->createJsonResponse($chartData);
    }

    protected function buildAjaxStatisticData($type, $cursor, $record, $dataFields)
    {
        list($startDate, $endDate) = DateToolkit::generateStartDateAndEndDate($type, $cursor, 'timestamp');
        $buildData = array();

        $totalDays = ($endDate - $startDate) / 86400 + 1;
        for ($i = 0; $i < $totalDays; ++$i) {
            $date = $startDate + 86400 * $i;
            $buildData['date'][$i] = date('Y-m-d', $date);
            $buildData['data'][$i] = empty($record[$date]) ? 0 : $record[$date][$dataFields];
        }
        $buildData['date'] = array_values($buildData['date']);
        $buildData['data'] = array_values($buildData['data']);

        return $buildData;
    }

    protected function prepareDepartmentLearnConditions($fields)
    {
        $conditions = array();
        if (empty($fields['courseCreatedTime'])) {
            list($startDateTime, $endDateTime) = DateToolkit::generateStartDateAndEndDate('year');
            $conditions['startDateTime'] = strtotime($startDateTime);
            $conditions['endDateTime'] = strtotime($endDateTime.' 23:59:59');
        } else {
            $date = explode('-', $fields['courseCreatedTime']);
            $conditions['startDateTime'] = strtotime($date[0]);
            $conditions['endDateTime'] = strtotime($date[1].' 23:59:59');
        }

        if (!empty($fields['postId'])) {
            $conditions['postId'] = $fields['postId'];
        }

        if (!empty($fields['categoryId'])) {
            $conditions['categoryId'] = $fields['categoryId'];
        }

        return $conditions;
    }

    protected function prepareSearchConditions($conditions)
    {
        if (empty($conditions['courseCreatedTime'])) {
            list($startDateTime, $endDateTime) = DateToolkit::generateStartDateAndEndDate('year');
            $conditions['startDateTime'] = strtotime($startDateTime);
            $conditions['endDateTime'] = strtotime($endDateTime.' 23:59:59');
        } else {
            $date = explode('-', $conditions['courseCreatedTime']);
            $conditions['startDateTime'] = strtotime($date[0]);
            $conditions['endDateTime'] = strtotime($date[1].' 23:59:59');
        }

        if (isset($conditions['orgIds']) && empty($conditions['orgIds'])) {
            $userIds = -1;
        } else {
            $orgIds = $this->prepareOrgIds($conditions);
            $userIds = $this->findUserIdsByOrgIds($orgIds);
            $userIds = empty($userIds) ? -1 : $userIds;
        }

        $keywordUserIds = array();
        if (isset($conditions['keywordType']) && !empty($conditions['keyword'])) {
            $users = $this->getUserService()->searchUsers(
                array(
                    $conditions['keywordType'] => $conditions['keyword'],
                ),
                array('id' => 'DESC'),
                0,
                PHP_INT_MAX
            );

            $keywordUserIds = empty($users) ? array(-1) : ArrayToolkit::column($users, 'id');
            unset($conditions['keywordType']);
            unset($conditions['keyword']);
        }

        if (-1 == $keywordUserIds || -1 == $userIds) {
            $conditions['userIds'] = array(-1);
        } elseif (empty($keywordUserIds)) {
            $conditions['userIds'] = $userIds;
        } else {
            $conditions['userIds'] = array_intersect($userIds, $keywordUserIds);
        }

        unset($conditions['orgIds']);

        return $conditions;
    }

    protected function buildCourseLearnStatisticCategoryData($request, $type = '')
    {
        $fields = $request->request->all();
        $orgIds = $this->prepareOrgIds($fields);
        $conditions = $this->prepareSearchConditions($fields);
        $categoryGroup = $this->getCategoryService()->getGroupByCode('course');
        $conditions['groupId'] = $categoryGroup['id'];
        $count = $this->getCategoryService()->countCategories($conditions);

        $paginator = new Paginator(
            $this->get('request'),
            $count,
            20
        );
        $paginator->setBaseUrl($this->generateUrl('admin_data_report_course_learn_data_statistic_category_list'));

        $categories = $this->getCategoryService()->searchCategories(
            $conditions,
            array(),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        if (1 == $paginator->getCurrentPage()) {
            $noneCategory = array(
                'id' => 0,
                'code' => 'none',
                'name' => $this->trans('my.department.course_learn_data.none_category'),
            );
            array_unshift($categories, $noneCategory);
        }

        $conditions['categoryIds'] = ArrayToolkit::column($categories, 'id');
        $learnRecords = $this->getDataStatisticsService()->statisticsLearnRecordGroupByCategoryId(
            $conditions
        );

        $learnRecords = ArrayToolkit::index($learnRecords, 'categoryId');

        return  array(
            'paginator' => $paginator,
            'categories' => $categories,
            'userNum' => $count,
            'learnRecords' => $learnRecords,
            'orgIds' => implode(',', $orgIds),
            'createdTimeData' => array('startDateTime' => $conditions['startDateTime'], 'endDateTime' => $conditions['endDateTime']),
        );
    }

    protected function buildCourseLearnStatisticPostData($request, $type = '')
    {
        $fields = $request->request->all();

        $orgIds = $this->prepareOrgIds($fields);
        $conditions = $this->prepareSearchConditions($fields);
        $count = $this->getPostService()->countPosts(
            array()
        );
        $paginator = new Paginator(
            $this->get('request'),
            $count,
            20
        );
        if ('ajax' != $type) {
            $paginator->setBaseUrl($this->generateUrl('admin_data_report_course_learn_data_statistic_post_list'));
        }

        $posts = $this->getPostService()->searchPosts(
            array(),
            array(),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        if (1 == $paginator->getCurrentPage()) {
            $nonePost = array(
                'id' => 0,
                'code' => 'none',
                'name' => $this->trans('my.department.course_learn_data.none_post'),
            );
            array_unshift($posts, $nonePost);
        }

        $conditions['postIds'] = ArrayToolkit::column($posts, 'id');
        $learnRecords = $this->getDataStatisticsService()->statisticsLearnRecordGroupByPostId(
            $conditions
        );
        $learnRecords = ArrayToolkit::index($learnRecords, 'postId');

        $postUserNums = $this->getUserService()->statisticsPostUserNumGroupByPostId();
        $postUserNums = ArrayToolkit::index($postUserNums, 'postId');

        return array(
            'paginator' => $paginator,
            'posts' => $posts,
            'userNum' => $count,
            'learnRecords' => $learnRecords,
            'postUserNums' => $postUserNums,
            'orgIds' => implode(',', $orgIds),
            'createdTimeData' => array('startDateTime' => $conditions['startDateTime'], 'endDateTime' => $conditions['endDateTime']),
        );
    }

    protected function buildCourseLearnStatisticDepartmentData($request, $type = '')
    {
        $fields = $request->request->all();
        $orgIds = $this->prepareOrgIds($fields);

        $count = $this->getOrgService()->countOrgs(
            array('orgIds' => $orgIds)
        );
        $paginator = new Paginator(
            $this->get('request'),
            $count,
            20
        );
        if ('ajax' != $type) {
            $paginator->setBaseUrl($this->generateUrl('admin_data_report_course_learn_data_statistic_department_list'));
        }

        $orgs = $this->getOrgService()->searchOrgs(
            array('orgIds' => $orgIds),
            array(),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $conditions = $this->prepareDepartmentLearnConditions($fields);
        $records = $this->getUserDailyLearnRecordService()->searchRecords(
            $conditions,
            array('id' => 'DESC'),
            0,
            PHP_INT_MAX
        );

        $recordIds = ArrayToolkit::column($records, 'id');

        $learnRecords = $this->getDataStatisticsService()->statisticsLearnRecordGroupByOrgId(array(
            'orgIds' => empty($orgIds) ? array(-1) : $orgIds,
            'recordIds' => empty($recordIds) ? array(-1) : $recordIds,
        ));
        $learnRecords = ArrayToolkit::index($learnRecords, 'orgId');

        $orgUserNums = $this->getUserService()->statisticsOrgUserNumGroupByOrgId();
        $orgUserNums = ArrayToolkit::index($orgUserNums, 'orgId');

        return  array(
            'paginator' => $paginator,
            'orgs' => $orgs,
            'userNum' => $count,
            'learnRecords' => $learnRecords,
            'orgIds' => implode(',', $orgIds),
            'orgUserNums' => $orgUserNums,
            'createdTimeData' => array('startDateTime' => $conditions['startDateTime'], 'endDateTime' => $conditions['endDateTime']),
        );
    }

    protected function findUserIdsByOrgIds($orgIds)
    {
        $usersOrg = $this->getUserOrgService()->findUserOrgsByOrgIds($orgIds);

        return ArrayToolkit::column($usersOrg, 'userId');
    }

    /**
     * @return UserDailyLearnRecordService
     */
    protected function getUserDailyLearnRecordService()
    {
        return $this->createService('CorporateTrainingBundle:UserDailyLearnRecord:UserDailyLearnRecordService');
    }

    protected function getDataStatisticsService()
    {
        return $this->createService('CorporateTrainingBundle:DataStatistics:DataStatisticsService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('CorporateTrainingBundle:User:UserService');
    }

    /**
     * @return CategoryService
     */
    protected function getCategoryService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineActivity:CategoryService');
    }

    /**
     * @return PostService
     */
    protected function getPostService()
    {
        return $this->createService('Post:PostService');
    }

    /**
     * @return OrgService
     */
    protected function getOrgService()
    {
        return $this->createService('CorporateTrainingBundle:Org:OrgService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return MemberService
     */
    protected function getOfflineActivityMemberService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineActivity:MemberService');
    }

    /**
     * @return UserOrgService
     */
    protected function getUserOrgService()
    {
        return $this->createService('CorporateTrainingBundle:User:UserOrgService');
    }

    /**
     * @return OfflineActivityService
     */
    protected function getOfflineActivityService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineActivity:OfflineActivityService');
    }

    /**
     * @return PostCourseService
     */
    protected function getPostCourseService()
    {
        return $this->createService('CorporateTrainingBundle:PostCourse:PostCourseService');
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }
}
