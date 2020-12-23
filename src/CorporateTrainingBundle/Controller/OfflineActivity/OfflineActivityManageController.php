<?php

namespace CorporateTrainingBundle\Controller\OfflineActivity;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\Admin\BaseController;
use CorporateTrainingBundle\Biz\OfflineActivity\Service\EnrollmentRecordService;
use CorporateTrainingBundle\Biz\OfflineActivity\Service\MemberService;
use CorporateTrainingBundle\Biz\OfflineActivity\Service\OfflineActivityService;
use CorporateTrainingBundle\Biz\ResourceScope\Service\ResourceAccessScopeService;
use CorporateTrainingBundle\Biz\ResourceScope\Service\ResourceVisibleScopeService;
use CorporateTrainingBundle\Common\OrgToolkit;
use Symfony\Component\HttpFoundation\Request;

class OfflineActivityManageController extends BaseController
{
    public function createAction(Request $request)
    {
        if (!$this->getOfflineActivityService()->hasActivityCreateRole()) {
            return $this->createMessageResponse('error', 'offline_activity.create.message.permission_error');
        }

        if ('POST' === $request->getMethod()) {
            $formData = $request->request->all();
            $formData = $this->buildOrgId($formData);
            if (!$this->getCurrentUser()->hasManagePermissionWithOrgCode($formData['orgCode'])) {
                return $this->createMessageResponse('error', 'admin.manage.org_no_permission');
            }
            $offlineActivity = $this->getOfflineActivityService()->createOfflineActivity($formData);
            $this->getResourceVisibleService()->setResourceVisibleScope($offlineActivity['id'], 'offlineActivity', array('showable' => 1, 'publishOrg' => $offlineActivity['orgId']));

            return $this->redirect($this->generateUrl('offline_activity_manage_base', array('id' => $offlineActivity['id'])));
        }

        return $this->render('offline-activity-manage/create.html.twig');
    }

    public function headerAction($offlineActivity)
    {
        if (!$this->getOfflineActivityService()->canManageOfflineActivity($offlineActivity['id'])) {
            return $this->createMessageResponse('error', 'offline_activity.manage.message.permission_error');
        }

        $category = $this->getCategoryService()->getCategory($offlineActivity['categoryId']);

        return $this->render(
            'offline-activity-manage/header.html.twig',
            array(
                'offlineActivity' => $offlineActivity,
                'category' => $category,
            )
        );
    }

    public function sidebarAction($offlineActivityId, $sideNav)
    {
        if (!$this->getOfflineActivityService()->canManageOfflineActivity($offlineActivityId)) {
            return $this->createMessageResponse('error', 'offline_activity.manage.message.permission_error');
        }

        $offlineActivity = $this->getOfflineActivityService()->getOfflineActivity($offlineActivityId);

        return $this->render(
            'offline-activity-manage/sidebar.html.twig',
            array(
                'offlineActivity' => $offlineActivity,
                'side_nav' => $sideNav,
            )
        );
    }

    public function baseAction(Request $request, $id)
    {
        if (!$this->getOfflineActivityService()->canManageOfflineActivity($id)) {
            return $this->createMessageResponse('error', 'offline_activity.manage.message.permission_error');
        }

        $offlineActivity = $this->getOfflineActivityService()->getOfflineActivity($id);
        if ('POST' === $request->getMethod()) {
            $data = $request->request->all();
            $data = $this->conversionTime($data, $offlineActivity);
            $data = $this->buildOrgId($data);
            $scope = $this->buildAccessScope($data);
            if (!$this->getCurrentUser()->hasManagePermissionWithOrgCode($data['orgCode'])) {
                return $this->createMessageResponse('error', 'admin.manage.org_no_permission');
            }
            $this->getResourceVisibleService()->setResourceVisibleScope($id, 'offlineActivity', $data);
            $this->getResourceAccessService()->setResourceAccessScope($id, 'offlineActivity', $scope);
            $this->getOfflineActivityService()->updateOfflineActivity($id, $data);
            $this->setFlashMessage('success', 'offline_activity.message.save_success');

            return $this->redirect($this->generateUrl('offline_activity_manage_base', array('id' => $id)));
        }

        $status = $this->decideStartTime($offlineActivity['startTime']);

        return $this->render(
            'offline-activity-manage/base.html.twig',
            array(
                'offlineActivity' => $offlineActivity,
                'status' => $status,
            )
        );
    }

    public function coverAction(Request $request, $id)
    {
        if (!$this->getOfflineActivityService()->canManageOfflineActivity($id)) {
            return $this->createMessageResponse('error', 'offline_activity.manage.message.permission_error');
        }

        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $this->getOfflineActivityService()->changeActivityCover($id, $data);

            return $this->redirect($this->generateUrl('offline_activity_manage_cover', array('id' => $id)));
        }

        $offlineActivity = $this->getOfflineActivityService()->getOfflineActivity($id);

        return $this->render(
            'offline-activity-manage/cover.html.twig',
            array(
                'offlineActivity' => $offlineActivity,
            )
        );
    }

    public function coverCropAction(Request $request, $id)
    {
        if (!$this->getOfflineActivityService()->canManageOfflineActivity($id)) {
            return $this->createMessageResponse('error', 'offline_activity.manage.message.permission_error');
        }

        $offlineActivity = $this->getOfflineActivityService()->getOfflineActivity($id);

        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $this->getOfflineActivityService()->changeActivityCover($offlineActivity['id'], json_decode($data['images'], true));

            return $this->redirect($this->generateUrl('offline_activity_manage_cover', array('id' => $offlineActivity['id'])));
        }

        $fileId = $request->getSession()->get('fileId');
        list($pictureUrl, $naturalSize, $scaledSize) = $this->getFileService()->getImgFileMetaInfo($fileId, 480, 270);

        return $this->render(
            'offline-activity-manage/cover-crop.html.twig',
            array(
                'offlineActivity' => $offlineActivity,
                'pictureUrl' => $pictureUrl,
                'naturalSize' => $naturalSize,
                'scaledSize' => $scaledSize,
            )
        );
    }

    public function listAction(Request $request, $id)
    {
        if (!$this->getOfflineActivityService()->canManageOfflineActivity($id)) {
            return $this->createMessageResponse('error', 'offline_activity.manage.message.permission_error');
        }

        $offlineActivity = $this->getOfflineActivityService()->getOfflineActivity($id);
        $conditions = $request->query->all();
        $conditions = $this->prepareConditions($conditions);
        $conditions['activityId'] = $id;
        $conditions['joinStatus'] = 'join';

        $memberNum = $this->getOfflineActivityMemberService()->countMembers($conditions);

        $paginator = new Paginator(
            $request,
            $memberNum,
            20
        );

        $members = $this->getOfflineActivityMemberService()->searchMembers(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = array();
        if (!empty($members)) {
            $userIds = ArrayToolkit::column($members, 'userId');
            $users = $this->getUserService()->findUsersByIds($userIds);
        }

        $members = $this->buildMemberOrgs($members);

        return $this->render(
            'offline-activity-manage/list.html.twig',
            array(
                'members' => $members,
                'offlineActivity' => $offlineActivity,
                'users' => $users,
                'paginator' => $paginator,
            )
        );
    }

    public function addMemberAction(Request $request, $id)
    {
        if (!$this->getOfflineActivityService()->canManageOfflineActivity($id)) {
            return $this->createMessageResponse('error', 'offline_activity.manage.message.permission_error');
        }

        $offlineActivity = $this->getOfflineActivityService()->getOfflineActivity($id);

        if ('POST' === $request->getMethod()) {
            $fields = $request->request->all();

            $user = $this->getUserService()->getUserByLoginField($fields['queryfield']);

            $member = $this->getOfflineActivityMemberService()->becomeMemberByImport($id, $user['id']);

            $result = false;

            if ($member) {
                $result = true;
            }

            return $this->createJsonResponse(array('success' => $result));
        }

        return $this->render(
            'offline-activity-manage/add-member-modal.html.twig',
            array(
                'offlineActivity' => $offlineActivity,
            )
        );
    }

    public function attendMemberAction(Request $request, $id)
    {
        $member = $this->getOfflineActivityMemberService()->getMember($id);

        if (empty($member)) {
            throw $this->createNotFoundException("member #{$id} not found");
        }

        if (!$this->getOfflineActivityService()->canManageOfflineActivity($member['offlineActivityId'])) {
            return $this->createMessageResponse('error', 'offline_activity.manage.message.permission_error');
        }

        if ('POST' === $request->getMethod()) {
            $fields = $request->request->all();
            $member = $this->getOfflineActivityMemberService()->attendMember($id, $fields['attendedStatus']);

            return $this->render(
                'offline-activity-manage/list-tr.html.twig',
                array(
                    'member' => $member,
                )
            );
        }

        return $this->render(
            'offline-activity-manage/attend-member-modal.html.twig',
            array(
                'member' => $member,
            )
        );
    }

    public function gradeMemberAction(Request $request, $id)
    {
        $member = $this->getOfflineActivityMemberService()->getMember($id);

        if (empty($member)) {
            throw $this->createNotFoundException("member #{$id} not found");
        }

        if (!$this->getOfflineActivityService()->canManageOfflineActivity($member['offlineActivityId'])) {
            return $this->createMessageResponse('error', 'offline_activity.manage.message.permission_error');
        }

        if ('POST' === $request->getMethod()) {
            $fields = $request->request->all();
            $member = $this->getOfflineActivityMemberService()->gradeMember($id, $fields);

            return $this->render(
                'offline-activity-manage/list-tr.html.twig',
                array(
                    'member' => $member,
                )
            );
        }

        return $this->render(
            'offline-activity-manage/grade-member-modal.html.twig',
            array(
                'member' => $member,
            )
        );
    }

    public function deleteMemberAction(Request $request, $id)
    {
        $member = $this->getOfflineActivityMemberService()->getMember($id);

        if (empty($member)) {
            throw $this->createNotFoundException("member #{$id} not found");
        }

        if (!$this->getOfflineActivityService()->canManageOfflineActivity($member['offlineActivityId'])) {
            return $this->createMessageResponse('error', 'offline_activity.manage.message.permission_error');
        }

        $result = $this->getOfflineActivityMemberService()->deleteMember($id);

        return $this->createJsonResponse($result);
    }

    public function checkMemberAction(Request $request, $id)
    {
        if (!$this->getOfflineActivityService()->canManageOfflineActivity($id)) {
            return $this->createMessageResponse('error', 'offline_activity.manage.message.permission_error');
        }

        $keyWord = $request->query->get('value');
        $user = $this->getUserService()->getUserByLoginField($keyWord);

        if (empty($user)) {
            $result = 'offline_activity.check_member.message.user_not_exist';
        } else {
            $result = $this->getOfflineActivityMemberService()->isMember($id, $user['id']);

            if ($result) {
                $result = 'offline_activity.check_member.message.user_repeated_sign_in';
            } else {
                $result = !$result;
            }
        }

        return $this->createJsonResponse($result);
    }

    public function statisticsAction(Request $request, $id)
    {
        if (!$this->getOfflineActivityService()->canManageOfflineActivity($id)) {
            return $this->createMessageResponse('error', 'offline_activity.manage.message.permission_error');
        }

        $offlineActivity = $this->getOfflineActivityService()->getOfflineActivity($id);

        return $this->render(
            'offline-activity-manage/statistics.html.twig',
            array(
                'offlineActivity' => $offlineActivity,
            )
        );
    }

    public function attendStatisticAction(Request $request, $id)
    {
        if (!$this->getOfflineActivityService()->canManageOfflineActivity($id)) {
            return $this->createMessageResponse('error', 'offline_activity.manage.message.permission_error');
        }

        $statistic = $this->getOfflineActivityMemberService()->statisticMembersAttendStatusByActivityId($id);

        if (empty($statistic)) {
            $result = array(
                array('name' => $this->trans('offline_activity.attend_status.unattend'), 'value' => 0),
            );

            return $this->createJsonResponse($result);
        }

        $replace = array(
            'none' => $this->trans('offline_activity.attend_status.unattend'),
            'attended' => $this->trans('offline_activity.attend_status.attend'),
            'unattended' => $this->trans('offline_activity.attend_status.absent'),
        );

        $result = $this->buildStatistic($replace, ArrayToolkit::index($statistic, 'attendedStatus'));

        return $this->createJsonResponse($result);
    }

    public function passStatisticAction(Request $request, $id)
    {
        if (!$this->getOfflineActivityService()->canManageOfflineActivity($id)) {
            return $this->createJsonResponse('error', 'offline_activity.manage.message.permission_error');
        }

        $statistic = $this->getOfflineActivityMemberService()->statisticMemberPassStatusByActivityId($id);

        if (empty($statistic)) {
            $result = array(
                array('name' => $this->trans('offline_activity.examination_status.not_examined'), 'value' => 0),
            );

            return $this->createJsonResponse($result);
        }

        $replace = array(
            'none' => $this->trans('offline_activity.examination_status.not_examined'),
            'passed' => $this->trans('offline_activity.examination_status.passed'),
            'unpassed' => $this->trans('offline_activity.examination_status.unpassed'),
        );

        $result = $this->buildStatistic($replace, ArrayToolkit::index($statistic, 'passedStatus'));

        return $this->createJsonResponse($result);
    }

    public function scoreStatisticAction(Request $request, $id)
    {
        if (!$this->getOfflineActivityService()->canManageOfflineActivity($id)) {
            return $this->createMessageResponse('error', 'offline_activity.manage.message.permission_error');
        }

        $statistic = $this->getOfflineActivityMemberService()->statisticMemberScoreByActivityId($id);

        $result = array();

        foreach ($statistic[0] as $key => $data) {
            $result[] = array(
                'scoreRange' => $key,
                'num' => empty($data) ? 0 : $data,
            );
        }

        return $this->createJsonResponse($result);
    }

    protected function buildStatistic($replace, $originData)
    {
        $result = array();

        foreach ($replace as $key => $value) {
            $result[] = array(
                'name' => $value,
                'value' => empty($originData[$key]['count']) ? 0 : (int) $originData[$key]['count'],
            );
        }

        return $result;
    }

    public function publishAction(Request $request, $id)
    {
        if (!$this->getOfflineActivityService()->canManageOfflineActivity($id)) {
            return $this->createMessageResponse('error', 'offline_activity.publish.message.permission_error');
        }

        $offlineActivity = $this->getOfflineActivityService()->getOfflineActivity($id);
        if ($offlineActivity['startTime']) {
            $this->getOfflineActivityService()->publishOfflineActivity($id);
            $success = true;
            $message = $this->renderActivityTr($id, $request)->getContent();
        } else {
            $success = false;
            $message = $this->trans('offline_activity.message.unfill');
        }

        return $this->createJsonResponse(array(
            'success' => $success,
            'message' => $message,
        ));
    }

    public function closeAction(Request $request, $id)
    {
        if (!$this->getOfflineActivityService()->canManageOfflineActivity($id)) {
            return $this->createMessageResponse('error', 'offline_activity.close.message.permission_error');
        }

        $this->getOfflineActivityService()->closeOfflineActivity($id);

        return $this->renderActivityTr($id, $request);
    }

    protected function renderActivityTr($activityId, $request)
    {
        $offlineActivity = $this->getOfflineActivityService()->getOfflineActivity($activityId);

        return $this->render('admin/offline-activity/offline-activity-table-tr.html.twig', array(
            'offlineActivity' => $offlineActivity,
        ));
    }

    protected function prepareConditions($conditions)
    {
        if (isset($conditions['keyword'])) {
            $conditions['keyword'] = trim($conditions['keyword']);
        }
        if (isset($conditions['keywordType']) && !empty($conditions['keyword'])) {
            $conditions[$conditions['keywordType']] = $conditions['keyword'];
            unset($conditions['keywordType']);
            unset($conditions['keyword']);
        }

        if (!empty($conditions)) {
            $users = $this->getUserService()->searchUsers(
                $conditions,
                array('id' => 'ASC'),
                0,
                PHP_INT_MAX
            );

            $users = ArrayToolkit::index($users, 'id');
            $userIds = ArrayToolkit::column($users, 'id');
            $conditions = array(
                'userIds' => empty($userIds) ? array(-1) : $userIds,
            );
        }

        return $conditions;
    }

    public function verifyListAction(Request $request, $id)
    {
        if (!$this->getOfflineActivityService()->hasActivityManageRole()) {
            return $this->createMessageResponse('error', 'offline_activity.manage.message.permission_error');
        }

        $offlineActivity = $this->getOfflineActivityService()->getOfflineActivity($id);
        $conditions = $request->query->all();
        $conditions = $this->prepareConditions($conditions);
        $conditions['status'] = $request->query->get('verifyStatus', 'submitted');
        if ('all' == $conditions['status']) {
            $conditions['status'] = '';
        }
        $conditions['offlineActivityId'] = $id;
        $recordCounts = $this->getEnrollmentRecordService()->countEnrollmentRecords($conditions);
        $paginator = new Paginator(
            $request,
            $recordCounts,
            20
        );

        $records = $this->getEnrollmentRecordService()->searchEnrollmentRecords(
            $conditions,
            array('submittedTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userIds = ArrayToolkit::column($records, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);
        $profiles = $this->getUserService()->findUserProfilesByIds($userIds);

        return $this->render(
            '@CorporateTraining/offline-activity-manage/verify-list.html.twig', array(
                'offlineActivity' => $offlineActivity,
                'records' => $records,
                'users' => $users,
                'profiles' => $profiles,
                'paginator' => $paginator,
            )
        );
    }

    public function verifyApplyAction(Request $request, $id)
    {
        if (!$this->getOfflineActivityService()->hasActivityManageRole()) {
            return $this->createMessageResponse('error', 'offline_activity.manage.message.permission_error');
        }

        $record = $this->getEnrollmentRecordService()->getEnrollmentRecord($id);

        $offlineActivity = $this->getOfflineActivityService()->getOfflineActivity($record['offlineActivityId']);

        if ('POST' === $request->getMethod()) {
            $fields = $request->request->all();
            if ('rejected' == $fields['verifyStatus']) {
                $this->getEnrollmentRecordService()->batchReject(array($record['id']), $fields);
            } elseif ('approved' == $fields['verifyStatus']) {
                $this->getEnrollmentRecordService()->batchPass(array($record['id']));
            }

            return $this->createJsonResponse(true);
        }

        return $this->render('@CorporateTraining/offline-activity-manage/verify-enrollment-apply.html.twig', array(
            'record' => $record,
            'offlineActivity' => $offlineActivity,
        ));
    }

    public function learnRecordAction(Request $request, $id)
    {
        if (!$this->getOfflineActivityService()->hasActivityManageRole()) {
            return $this->createMessageResponse('error', 'offline_activity.manage.message.permission_error');
        }

        $record = $this->getEnrollmentRecordService()->getEnrollmentRecord($id);
        $user = $this->getUserService()->getUser($record['userId']);
        $post = $this->getPostService()->getPost($user['postId']);
        $userInfo = array(
            'id' => $user['id'],
            'name' => $user['nickname'],
            'post' => $post['name'],
            'orgIds' => $user['orgIds'],
        );

        $postCourseDatas = $this->calculatePostCoursesDatas($post['id'], $user['id']);

        return $this->render('@CorporateTraining/offline-activity-manage/record-enrollment-apply.html.twig', array(
            'userInfo' => $userInfo,
            'postCourseCount' => $postCourseDatas['postCourseCount'],
            'totalLearnTime' => $postCourseDatas['totalLearnTime'],
            'learnedCoursesNum' => $postCourseDatas['learnedCoursesNum'],
            'tab_types' => $request->get('tab_types', 'assignmentRecord'),
        ));
    }

    protected function calculatePostCoursesDatas($postId, $userId)
    {
        $postCourses = $this->getPostCourseService()->findPostCoursesByPostId($postId);

        if (!empty($postCourses)) {
            $courseIds = ArrayToolkit::column($postCourses, 'courseId');
            $courses = $this->getCourseService()->findCoursesByIds($courseIds);
            $totalLearnTime = $this->calculatePostCoursesLearnTime($userId, $courses);
            $learnedCoursesNum = $this->getPostCourseService()->countUserLearnedPostCourses($postId, $courseIds);
        }

        return array(
            'postCourseCount' => empty($postCourses) ? 0 : count($postCourses),
            'totalLearnTime' => empty($totalLearnTime) ? 0 : $totalLearnTime,
            'learnedCoursesNum' => empty($learnedCoursesNum) ? 0 : $learnedCoursesNum,
        );
    }

    protected function calculatePostCoursesLearnTime($userId, $courses)
    {
        foreach ($courses as $key => &$course) {
            $course['learnTime'] = $this->getTaskResultService()->sumLearnTimeByCourseIdAndUserId($key, $userId);
        }
        $learnTime = ArrayToolkit::column($courses, 'learnTime');

        return array_sum($learnTime);
    }

    public function batchAuditAction(Request $request)
    {
        return $this->render(
            'offline-activity-manage/batch-audit-modal.html.twig'
        );
    }

    public function batchPassAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $recordIds = $request->request->get('recordIds');
            $this->getEnrollmentRecordService()->batchPass(explode(',', $recordIds));

            return $this->createJsonResponse(true);
        }
    }

    public function batchRejectAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $recordIds = $request->request->get('recordIds');
            $this->getEnrollmentRecordService()->batchReject(explode(',', $recordIds));

            return $this->createJsonResponse(true);
        }
    }

    protected function conversionTime($data, $offlineActivity)
    {
        if (array_key_exists('startTime', $data)) {
            $data['startTime'] = strtotime($data['startTime']);
        } else {
            $data['startTime'] = $offlineActivity['startTime'];
        }

        if (array_key_exists('endTime', $data)) {
            $data['endTime'] = strtotime($data['endTime']);
        } else {
            $data['endTime'] = $offlineActivity['endTime'];
        }

        if (array_key_exists('enrollmentEndDate', $data)) {
            $data['enrollmentEndDate'] = strtotime($data['enrollmentEndDate']);
        } else {
            $data['enrollmentEndDate'] = $offlineActivity['enrollmentEndDate'];
        }

        if (array_key_exists('enrollmentStartDate', $data)) {
            $data['enrollmentStartDate'] = strtotime($data['enrollmentStartDate']);
        } else {
            $data['enrollmentStartDate'] = $offlineActivity['enrollmentStartDate'];
        }

        return $data;
    }

    protected function decideStartTime($startTime)
    {
        if ($startTime && time() >= $startTime) {
            $status = true;
        } else {
            $status = false;
        }

        return $status;
    }

    public function viewSignQrcodeAction(Request $request, $activityId)
    {
        if (!$this->getOfflineActivityService()->canManageOfflineActivity($activityId)) {
            return $this->createMessageResponse('error', 'offline_activity.QR_code.message.permission_error');
        }

        $user = $this->getCurrentUser();
        $offlineActivity = $this->getOfflineActivityService()->getOfflineActivity($activityId);
        if (empty($offlineActivity)) {
            return $this->createMessageResponse('error', 'offline_activity.message.not_exist_error');
        }
        $qrcodeImgUrl = $this->qrcode($activityId, $user['id']);

        return $this->render('offline-activity/sign-in/qr-code.html.twig',
            array(
                'offlineActivity' => $offlineActivity,
                'qrcodeImgUrl' => $qrcodeImgUrl,
            )
        );
    }

    public function evaluateShowAction(Request $request, $memberId)
    {
        if (!$this->getOfflineActivityService()->hasActivityManageRole()) {
            return $this->createMessageResponse('error', 'offline_activity.evaluate.message.permission_error');
        }

        $member = $this->getOfflineActivityMemberService()->getMember($memberId);

        return $this->render('offline-activity-manage/evaluate-modal.html.twig',
            array('member' => $member)
        );
    }

    protected function qrcode($activityId, $userId)
    {
        $token = $this->getTokenService()->makeToken('qrcode', array(
            'data' => array(
                'url' => $this->generateUrl('offline_activity_sign_in', array('activityId' => $activityId), true),
            ),
            'duration' => 3600 * 24 * 365,
        ));

        $url = $this->generateUrl('common_parse_qrcode', array('token' => $token['token']), true);

        return $this->generateUrl('common_qrcode', array('text' => $url), true);
    }

    protected function buildMemberOrgs($members)
    {
        foreach ($members as $key => $member) {
            $user = $this->getUserService()->getUser($member['userId']);
            $orgs = $this->getOrgService()->findOrgsByIds($user['orgIds']);
            $members[$key]['orgsName'] = OrgToolkit::buildOrgsNames($user['orgIds'], $orgs);
            $members[$key]['org'] = empty($orgs[0]) ? array() : $orgs[0];
        }

        return $members;
    }

    protected function buildOrgId($fields)
    {
        if (!empty($fields['orgCode']) && empty($fields['orgId'])) {
            $org = $this->getOrgService()->getOrgByOrgCode($fields['orgCode']);
            $fields['orgId'] = empty($org) ? 1 : $org['id'];
        }

        return $fields;
    }

    /**
     * @return TokenService
     */
    protected function getTokenService()
    {
        return $this->createService('User:TokenService');
    }

    /**
     * @return OfflineActivityService
     */
    protected function getOfflineActivityService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineActivity:OfflineActivityService');
    }

    /**
     * @return MemberService
     */
    protected function getOfflineActivityMemberService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineActivity:MemberService');
    }

    /**
     * @return EnrollmentRecordService
     */
    protected function getEnrollmentRecordService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineActivity:EnrollmentRecordService');
    }

    /**
     * @return FileService
     */
    protected function getFileService()
    {
        return $this->createService('Content:FileService');
    }

    /**
     * @return PostService
     */
    protected function getPostService()
    {
        return $this->createService('CorporateTrainingBundle:Post:PostService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('CorporateTrainingBundle:User:UserService');
    }

    /**
     * @return OrgService
     */
    protected function getOrgService()
    {
        return $this->createService('Org:OrgService');
    }

    protected function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    protected function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }

    protected function getPostCourseService()
    {
        return $this->createService('CorporateTrainingBundle:PostCourse:PostCourseService');
    }

    protected function getProjectPlanService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:ProjectPlanService');
    }

    protected function getProjectPlanMemberService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:MemberService');
    }

    protected function createAssignmentStrategy()
    {
        return $this->getBiz()->offsetGet('projectPlan_item_strategy_context')->createStrategy('course');
    }

    /**
     * @return ResourceVisibleScopeService
     */
    protected function getResourceVisibleService()
    {
        return $this->createService('CorporateTrainingBundle:ResourceScope:ResourceVisibleScopeService');
    }

    /**
     * @return ResourceAccessScopeService
     */
    protected function getResourceAccessService()
    {
        return $this->createService('CorporateTrainingBundle:ResourceScope:ResourceAccessScopeService');
    }
}
