<?php

namespace CorporateTrainingBundle\Controller\ProjectPlan;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Exception\AccessDeniedException;
use AppBundle\Common\Paginator;
use AppBundle\Controller\BaseController;
use CorporateTrainingBundle\Biz\ResourceScope\Service\ResourceAccessScopeService;
use CorporateTrainingBundle\Biz\ResourceScope\Service\ResourceVisibleScopeService;
use ExamPlugin\Biz\TestPaper\Service\TestPaperService;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;

class ProjectPlanManageController extends BaseController
{
    public function createAction(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$this->getProjectPlanService()->hasManageProjectPlanPermission()) {
            return $this->createMessageResponse('error', 'project_plan.message.can_not_create_message');
        }

        return $this->render('project-plan/create.html.twig', array(
                'user' => $user,
            )
        );
    }

    public function overviewBoardAction($id)
    {
        if (!$this->getProjectPlanService()->canManageProjectPlan($id)) {
            return $this->createMessageResponse('error', 'project_plan.message.can_not_manage_message');
        }

        $projectPlan = $this->getProjectPlanService()->getProjectPlan($id);
        $conditions['projectPlanId'] = $projectPlan['id'];

        $projectPlanMemberNum = $this->getProjectPlanMemberService()->countProjectPlanMembers($conditions);

        if (1 == $projectPlan['requireAudit']) {
            $recordCounts = $this->getProjectPlanMemberService()->countEnrollmentRecords(array('projectPlanId' => $projectPlan['id'], 'status' => 'submitted'));
        }

        $projectPlan['progress'] = $this->getProjectPlanService()->getProjectPlanProgress($projectPlan['id']);

        $projectPlanItems = $this->getProjectPlanService()->searchProjectPlanItems(array('projectPlanId' => $projectPlan['id']), array(), 0, PHP_INT_MAX);

        $detail = $this->getProjectPlanService()->findTaskDetailByTimeRangeAndProjectPlanId(strtotime(date('Y-m-d', time())), strtotime(date('Y-m-d 23:59:59', time())), $projectPlan['id']);

        return $this->render('project-plan/overview-board.html.twig', array(
                'projectPlan' => $projectPlan,
                'projectPlanItems' => $projectPlanItems,
                'projectPlanMemberNum' => $projectPlanMemberNum,
                'recordCounts' => !empty($recordCounts) ? $recordCounts : 0,
                'detail' => $detail,
                'time' => time(),
            )
        );
    }

    public function itemsDetailAction($projectPlanId)
    {
        $projectPlanItems = $this->getProjectPlanService()->searchProjectPlanItems(array('projectPlanId' => $projectPlanId), array(), 0, PHP_INT_MAX);
        $projectPlanItems = $this->getManageUrl($projectPlanItems);

        return $this->createJsonResponse($projectPlanItems);
    }

    public function baseAction(Request $request, $id)
    {
        if (!$this->getProjectPlanService()->canManageProjectPlan($id)) {
            return $this->createMessageResponse('error', 'project_plan.message.can_not_manage_message');
        }

        $projectPlan = $this->getProjectPlanService()->getProjectPlan($id);

        if ('POST' == $request->getMethod()) {
            $fields = $request->request->all();
            $org = $this->getOrgService()->getOrgByOrgCode($fields['orgCode']);
            if (!empty($data['orgCode']) && !$this->getCurrentUser()->hasManagePermissionWithOrgCode($fields['orgCode'])) {
                throw new AccessDeniedException($this->trans('admin.manage.org_no_permission'));
            }
            $fields['orgId'] = $org['id'];
            $fields = $this->conversionTime($fields, $projectPlan);
            if (0 == $fields['requireEnrollment']) {
                $fields['requireAudit'] = 0;
            }

            $this->getResourceVisibleScopeService()->setResourceVisibleScope($id, 'projectPlan', $fields);

            $this->getProjectPlanService()->updateProjectPlan($id, $fields);
            $this->setFlashMessage('success', 'project_plan.message.update_success_message');

            return $this->redirect($this->generateUrl('project_plan_manage_base', array('id' => $id)));
        }

        return $this->render('project-plan/base.html.twig', array(
                'projectPlan' => $projectPlan,
            )
        );
    }

    public function coverAction(Request $request, $id)
    {
        if (!$this->getProjectPlanService()->canManageProjectPlan($id)) {
            return $this->createMessageResponse('error', 'project_plan.message.can_not_manage_message');
        }

        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $this->getProjectPlanService()->changeCover($id, $data);

            return $this->redirect($this->generateUrl('project_plan_manage_cover', array('id' => $id)));
        }

        $projectPlan = $this->getProjectPlanService()->getProjectPlan($id);

        return $this->render('project-plan/cover.html.twig', array(
                'projectPlan' => $projectPlan,
            )
        );
    }

    public function coverCropAction(Request $request, $id)
    {
        if (!$this->getProjectPlanService()->canManageProjectPlan($id)) {
            return $this->createMessageResponse('error', 'project_plan.message.can_not_manage_message');
        }

        $projectPlan = $this->getProjectPlanService()->getProjectPlan($id);

        if ('POST' == $request->getMethod()) {
            $data = $request->request->all();
            $this->getProjectPlanService()->changeCover($projectPlan['id'],
                json_decode($data['images'], true));

            return $this->redirect($this->generateUrl('project_plan_manage_cover',
                array('id' => $projectPlan['id'])));
        }

        $fileId = $request->getSession()->get('fileId');
        list($pictureUrl, $naturalSize, $scaledSize) = $this->getFileService()->getImgFileMetaInfo($fileId, 480, 270);

        return $this->render(
            'project-plan/cover-crop.html.twig',
            array(
                'projectPlan' => $projectPlan,
                'pictureUrl' => $pictureUrl,
                'naturalSize' => $naturalSize,
                'scaledSize' => $scaledSize,
            )
        );
    }

    public function headerAction($projectPlan)
    {
        $user = $this->getCurrentUser();
        if (!$this->getProjectPlanService()->canManageProjectPlan($projectPlan['id']) && !$user->hasPermission('admin_data_center_project_plan')) {
            throw $this->createAccessDeniedException('No project plan management permissions');
        }

        return $this->render('project-plan/header.html.twig', array(
                'projectPlan' => $projectPlan,
            )
        );
    }

    public function sidebarAction($id, $sideNav, $tabNav)
    {
        $user = $this->getCurrentUser();
        if (!$this->getProjectPlanService()->canManageProjectPlan($id) && !$user->hasPermission('admin_data_center_project_plan')) {
            throw $this->createAccessDeniedException('No project plan management permissions');
        }

        $projectPlan = $this->getProjectPlanService()->getProjectPlan($id);

        return $this->render('project-plan/sidebar.html.twig', array(
                'projectPlan' => $projectPlan,
                'side_nav' => $sideNav,
                'tab_nav' => $tabNav,
            )
        );
    }

    public function ajaxPublishAction(Request $request, $id)
    {
        if (!$this->getProjectPlanService()->canManageProjectPlan($id)) {
            return $this->createJsonResponse(array('success' => 'false', 'message' => ServiceKernel::instance()->trans('project_plan.publish.message.no_permission')));
        }

        $projectPlan = $this->getProjectPlanService()->getProjectPlan($id);
        if (empty($projectPlan)) {
            return $this->createJsonResponse(array('success' => 'false', 'message' => ServiceKernel::instance()->trans('project_plan.message.project_plan_empty')));
        }

        if (!$projectPlan['startTime']) {
            $success = false;
            $message = ServiceKernel::instance()->trans('project_plan.publish.message.data_empty');
        }

        if ($projectPlan['showable'] && !$projectPlan['enrollmentStartDate']) {
            $success = false;
            $message = ServiceKernel::instance()->trans('project_plan.publish.message.data_empty');
        } else {
            $this->getProjectPlanService()->publishProjectPlan($id);
            $success = true;
            $message = '';
        }

        return $this->createJsonResponse(array('success' => $success, 'message' => $message));
    }

    public function ajaxCloseAction(Request $request, $id)
    {
        if (!$this->getProjectPlanService()->canManageProjectPlan($id)) {
            return $this->createJsonResponse(false);
        }

        $projectPlan = $this->getProjectPlanService()->getProjectPlan($id);

        if (empty($projectPlan)) {
            return $this->createJsonResponse(false);
        }

        $result = $this->getProjectPlanService()->closeProjectPlan($id);
        if ($result) {
            return $this->createJsonResponse(true);
        }

        return $this->createJsonResponse(false);
    }

    public function ajaxArchiveAction(Request $request, $id)
    {
        if (!$this->getProjectPlanService()->canManageProjectPlan($id)) {
            return $this->createJsonResponse(array('success' => 'false', 'message' => ServiceKernel::instance()->trans('project_plan.archive.message.no_permission')));
        }
        $projectPlan = $this->getProjectPlanService()->getProjectPlan($id);
        if (empty($projectPlan)) {
            return $this->createJsonResponse(array('success' => 'false', 'message' => ServiceKernel::instance()->trans('project_plan.message.project_plan_empty')));
        }

        $this->getProjectPlanService()->archiveProjectPlan($id);

        return $this->createJsonResponse(array('success' => true, 'message' => ''));
    }

    public function ajaxRemoveAction(Request $request, $id)
    {
        if (!$this->getProjectPlanService()->canManageProjectPlan($id)) {
            return $this->createJsonResponse(false);
        }

        $projectPlan = $this->getProjectPlanService()->getProjectPlan($id);

        if (empty($projectPlan)) {
            return $this->createJsonResponse(false);
        }

        $result = $this->getProjectPlanService()->deleteProjectPlan($id);

        if ($result) {
            return $this->createJsonResponse(true);
        }

        return $this->createJsonResponse(false);
    }

    public function itemRemoveAction(Request $request, $id)
    {
        $projectPlanItem = $this->getProjectPlanService()->getProjectPlanItem($id);

        if (!$this->getProjectPlanService()->canManageProjectPlan($projectPlanItem['projectPlanId'])) {
            return $this->createJsonResponse(array('success' => 'false', 'message' => ServiceKernel::instance()->trans('project_plan.message.can_not_manage_message')));
        }

        $result = $this->getProjectPlanService()->deleteProjectPlanItem($id);

        if ($result) {
            return $this->createJsonResponse(true);
        }

        return $this->createJsonResponse(false);
    }

    public function itemsAction(Request $request, $id)
    {
        if (!$this->getProjectPlanService()->canManageProjectPlan($id)) {
            return $this->createMessageResponse('error', 'project_plan.message.can_not_manage_message');
        }
        $projectPlan = $this->getProjectPlanService()->getProjectPlan($id);

        $projectPlanItems = $this->getProjectPlanService()->searchProjectPlanItems(
            array('projectPlanId' => $id),
            array('seq' => 'ASC'),
            0,
            PHP_INT_MAX
        );

        return $this->render(
            '@App/project-plan/item-manage.html.twig',
            array(
                'projectPlanItems' => $projectPlanItems,
                'projectPlan' => $projectPlan,
            )
        );
    }

    public function itemTypeChooseAction(Request $request, $id)
    {
        return $this->render('project-plan/item/choose-modal.html.twig', array('id' => $id));
    }

    public function itemAddAction(Request $request, $id, $type)
    {
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $message = $this->validateItem($data, $type);
            if ($message) {
                return $this->createJsonResponse(array('success' => false, 'message' => $message));
            }

            $this->getProjectPlanService()->setProjectPlanItems($id, $data, $type);

            return $this->createJsonResponse(array('success' => true, 'message' => ''));
        }

        $projectPlanMetas = $this->getProjectPlanMetas();

        return $this->forward(
            $projectPlanMetas[$type]['controller'].':create',
            array(
                'projectPlanId' => $id,
            )
        );
    }

    public function itemUpdateAction(Request $request, $id, $projectPlanId)
    {
        $item = $this->getProjectPlanService()->getProjectPlanItem($id);

        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $data['projectPlanId'] = $projectPlanId;
            $message = $this->validateItem($data, $item['targetType']);
            if ($message) {
                return $this->createJsonResponse(array('success' => false, 'message' => $message));
            }

            $this->getProjectPlanService()->updatePlanItem($id, $data, $item['targetType']);

            return $this->createJsonResponse(array('success' => true, 'message' => ''));
        }

        $projectPlanMetas = $this->getProjectPlanMetas();

        return $this->forward(
            $projectPlanMetas[$item['targetType']]['controller'].':update',
            array(
                'id' => $id,
            )
        );
    }

    public function itemDeleteAction(Request $request, $id)
    {
        $item = $this->getProjectPlanService()->getProjectPlanItem($id);

        $this->getProjectPlanService()->deleteProjectPlanItem($id);

        return $this->createJsonResponse(true);
    }

    public function itemListAction(Request $request, $id, $type)
    {
        $projectPlanMetas = $this->getProjectPlanMetas();

        return $this->forward(
            $projectPlanMetas[$type]['controller'].':list',
            array(
                'request' => $request,
                'id' => $id,
            )
        );
    }

    public function enrollAction(Request $request, $id)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException();
        }
        $projectPlan = $this->getProjectPlanService()->getProjectPlan($id);

        if ('published' != $projectPlan['status']) {
            throw $this->createAccessDeniedException('Project plan not published');
        }

        if (!empty($projectPlan['requireEnrollment'])) {
            if ($projectPlan['conditionalAccess'] && !$this->getResourceAccessService()->canUserAccessResource('projectPlan', $projectPlan['id'], $user['id'])) {
                return $this->render(
                    '@CorporateTraining/project-plan/can-not-enroll-modal.html.twig', array()
                );
            }
            if (empty($projectPlan['requireAudit'])) {
                return $this->redirectToRoute('project_plan_attend', array('id' => $id));
            } else {
                return $this->redirectToRoute('project_plan_apply', array('id' => $id));
            }
        } else {
            throw new AccessDeniedException('Project plan did not open registration');
        }
    }

    public function attendAction(Request $request, $id)
    {
        $user = $this->getCurrentUser();
        $canAccess = true;
        $projectPlan = $this->getProjectPlanService()->getProjectPlan($id);
        $advancedOption = $this->getProjectPlanService()->getProjectPlanAdvancedOptionByProjectPlanId($id);
        $noneRecord = $this->createNoneEnrollmentRecord($user['id'], $id);
        if ($projectPlan['conditionalAccess']) {
            $canAccess = $this->getResourceAccessService()->canUserAccessResource('projectPlan', $projectPlan['id'], $user['id']);
        }

        if ('POST' === $request->getMethod()) {
            if (!$canAccess) {
                return $this->createAccessDeniedException($this->trans('resource_scope.no_permission'));
            }
            $fields = $request->request->all();
            $result = $this->getProjectPlanMemberService()->attend($id, $user['id'], $noneRecord['id'], $fields);
            $attachments = $request->request->get('attachment');
            $this->uploadMaterialAttachments($attachments, $noneRecord['id']);

            return $this->createJsonResponse($result);
        }

        return $this->render(
            '@CorporateTraining/project-plan/detail/attend-modal.html.twig',
            array(
                'projectPlan' => $projectPlan,
                'advancedOption' => empty($advancedOption) ? array() : $advancedOption,
                'noneRecord' => $noneRecord,
                'canAccess' => $canAccess,
            )
        );
    }

    public function applyAction(Request $request, $id)
    {
        $user = $this->getCurrentUser();
        $projectPlan = $this->getProjectPlanService()->getProjectPlan($id);
        $advancedOption = $this->getProjectPlanService()->getProjectPlanAdvancedOptionByProjectPlanId($id);
        $rejectedRecord = $this->getProjectPlanMemberService()->searchEnrollmentRecords(array('status' => 'rejected', 'userId' => $user['id'], 'projectPlanId' => $id), array('updatedTime' => 'DESC'), 0, 1);
        $noneRecord = $this->createNoneEnrollmentRecord($user['id'], $id);

        if ('POST' === $request->getMethod()) {
            $attachments = $request->request->get('attachment');

            $this->uploadMaterialAttachments($attachments, $noneRecord['id']);

            $fields = array(
                'remark' => $request->request->get('remark'),
            );
            $result = $this->getProjectPlanService()->applyAttendProjectPlan($noneRecord['id'], $fields);

            return $this->createJsonResponse(true);
        }

        return $this->render(
            '@CorporateTraining/project-plan/detail/apply-modal.html.twig',
            array(
                'projectPlan' => $projectPlan,
                'advancedOption' => empty($advancedOption) ? array() : $advancedOption,
                'noneRecord' => empty($noneRecord) ? array() : $noneRecord,
                'rejectedRecord' => empty($rejectedRecord) ? array() : $rejectedRecord[0],
            )
        );
    }

    public function viewQrcodeAction($id)
    {
        $projectPlan = $this->getProjectPlanService()->getProjectPlan($id);

        $qrcodeImgUrl = $this->qrcode('project_plan_detail', array('id' => $projectPlan['id']));

        return $this->render('project-plan/qr-code.html.twig',
            array(
                'projectPlan' => $projectPlan,
                'qrcodeImgUrl' => $qrcodeImgUrl,
            )
        );
    }

    public function detailAction(Request $request, $id)
    {
        $canAccess = true;
        if (!$this->getProjectPlanService()->canUserVisitResource($id)) {
            return $this->createMessageResponse('error', $this->trans('resource.no_permission'));
        }

        $projectPlan = $this->getProjectPlanService()->getProjectPlan($id);
        $projectPlan['studentNum'] = $this->getProjectPlanMemberService()->countProjectPlanMembers(array('projectPlanId' => $id));

        $projectPlanItems = $this->getProjectPlanService()->searchProjectPlanItems(
            array('projectPlanId' => $id),
            array('seq' => 'ASC'),
            0,
            PHP_INT_MAX
        );
        if ($projectPlan['conditionalAccess']) {
            $canAccess = $this->getResourceAccessService()->canUserAccessResource('projectPlan', $projectPlan['id'], $this->getCurrentUser()->getId());
        }

        return $this->render(
            '@CorporateTraining/project-plan/detail/detail.html.twig',
            array(
                'projectPlan' => $projectPlan,
                'projectPlanItems' => $projectPlanItems,
                'canAccess' => $canAccess,
            )
        );
    }

    public function courseTaskListAction(Request $request, $projectPlanItem, $projectPlanId)
    {
        $projectPlanItem = $this->getProjectPlanService()->getProjectPlanItem($projectPlanItem['id']);

        if ('course' == $projectPlanItem['targetType']) {
            $course = $this->getCourseService()->getCourse($projectPlanItem['targetId']);
            $courseItems = $this->getCourseService()->findCourseItems($course['id']);
        }

        return $this->render(
            'project-plan/detail/task-list/list.html.twig',
            array(
                'projectPlanId' => $projectPlanId,
                'projectPlanItem' => $projectPlanItem,
                'course' => empty($course) ?: $course,
                'courseItems' => empty($courseItems) ?: $courseItems,
                'currentTime' => time(),
            )
        );
    }

    public function advancedOptionAction(Request $request, $id)
    {
        $projectPlan = $this->getProjectPlanService()->getProjectPlan($id);
        $advancedOption = $this->getProjectPlanService()->getProjectPlanAdvancedOptionByProjectPlanId($id);

        if ('POST' === $request->getMethod()) {
            $fields = $request->request->all();
            $fields['projectPlanId'] = $id;
            $fields = $this->filterAdvancedOption($fields);
            $scopes = $this->buildAccessScope($fields);
            $this->getResourceAccessService()->setResourceAccessScope($id, 'projectPlan', $scopes);
            if (empty($advancedOption)) {
                $result = $this->getProjectPlanService()->createProjectPlanAdvancedOption($fields);
            } else {
                $result = $this->getProjectPlanService()->updateProjectPlanAdvancedOption($advancedOption['id'], $fields);
            }

            $this->getProjectPlanService()->updateProjectPlan($projectPlan['id'], array('conditionalAccess' => $fields['conditionalAccess']));

            return $this->createJsonResponse($result);
        }

        $materials = empty($advancedOption) ? array() : array($advancedOption['material2'], $advancedOption['material3']);

        return $this->render(
            '@CorporateTraining/project-plan/advanced-options-modal.html.twig',
            array(
                'projectPlan' => $projectPlan,
                'advancedOption' => $advancedOption,
                'materials' => $materials,
            )
        );
    }

    public function crowdMatchAction(Request $request)
    {
        $queryString = $request->query->get('q');
        $attributes = array('userGroup', 'org', 'post');
        $name = $this->getUserAttributeService()->searchAttributesName($attributes, $queryString);

        return $this->createJsonResponse($name);
    }

    public function projectPlanListAction(Request $request)
    {
        $conditions = $request->query->all();
        $conditions['requireEnrollment'] = 1;
        $conditions['status'] = 'published';
        $conditions = $this->buildProjectPlanListConditions($conditions);

        $count = $this->getProjectPlanService()->countProjectPlans(
            $conditions
        );

        $paginator = new Paginator(
            $request,
            $count,
            20
        );

        $projectPlans = $this->getProjectPlanService()->searchProjectPlans(
            $conditions,
            array('startTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        foreach ($projectPlans as &$projectPlan) {
            $projectPlan['canAccess'] = true;
            if ($projectPlan['conditionalAccess']) {
                $projectPlan['canAccess'] = $this->getResourceAccessService()->canUserAccessResource('projectPlan', $projectPlan['id'], $this->getCurrentUser()->getId());
            }
        }

        return $this->render(
            '@CorporateTraining/project-plan/list.html.twig',
            array(
                'enrollment' => empty($conditions['enrollment']) ? 0 : $conditions['enrollment'],
                'projectPlans' => $projectPlans,
                'paginator' => $paginator,
            )
        );
    }

    public function sortItemAction(Request $request)
    {
        $ids = $request->request->get('ids');

        if (!empty($ids)) {
            $this->getProjectPlanService()->sortItems($ids);
        }

        return $this->createJsonResponse(true);
    }

    public function viewExamQrcodeAction($id, $examId)
    {
        $exam = $this->getExamService()->getExam($examId);

        $qrcodeImgUrl = $this->qrcode('can_do_exam', array('id' => $id, 'examId' => $examId));

        return $this->render('project-plan/exam-manage/qrcode/qr-code.html.twig',
            array(
                'exam' => $exam,
                'qrcodeImgUrl' => $qrcodeImgUrl,
            )
        );
    }

    public function canDoExamAction($id, $examId)
    {
        $user = $this->getCurrentUser();
        $member = $this->getProjectPlanMemberService()->getProjectPlanMemberByUserIdAndProjectPlanId($user['id'], $id);
        if (empty($member)) {
            return $this->render('project-plan/exam-manage/qrcode/error.html.twig',
                array(
                    'message' => ServiceKernel::instance()->trans('project_plan.can_do_exam.message.no_permission'),
                )
            );
        }

        $examMember = $this->getExamService()->getMemberByExamIdIdAndUserId($examId, $user['id']);
        if (empty($examMember)) {
            $this->getExamService()->createMember(array('examId' => $examId, 'userId' => $user['id']));
        }

        return $this->redirect(
            $this->generateUrl('exam_show', array('id' => $examId))
        );
    }

    protected function getManageUrl($projectPlanItems)
    {
        foreach ($projectPlanItems as &$projectPlanItem) {
            switch ($projectPlanItem['targetType']) {
                case 'offline_course':
                    $projectPlanItem['url'] = $this->generateUrl('project_plan_item_manage_list', array('id' => $projectPlanItem['projectPlanId'], 'type' => 'offlineCourse'));
                    break;
                case 'exam':
                    $projectPlanItem['url'] = $this->generateUrl('project_plan_exam_manage_list', array('id' => $projectPlanItem['projectPlanId'], 'type' => 'exam'));
                    break;
                case 'offline_exam':
                    $projectPlanItem['url'] = $this->generateUrl('project_plan_offline_exam_manage_list', array('id' => $projectPlanItem['projectPlanId']));
                    break;
                default:
                    $projectPlanItem['url'] = $this->generateUrl('project_plan_item_manage_list', array('id' => $projectPlanItem['projectPlanId'], 'type' => 'course'));
            }
        }

        return $projectPlanItems;
    }

    protected function createNoneEnrollmentRecord($userId, $projectPlanId)
    {
        $noneRecord = $this->getProjectPlanMemberService()->searchEnrollmentRecords(array('status' => 'none', 'userId' => $userId, 'projectPlanId' => $projectPlanId), array('updatedTime' => 'DESC'), 0, 1);

        if (empty($noneRecord)) {
            $fields = array(
                'userId' => $userId,
                'projectPlanId' => $projectPlanId,
                'status' => 'none',
            );

            $noneRecord = $this->getProjectPlanMemberService()->createEnrollmentRecord($fields);
        } else {
            $noneRecord = $noneRecord[0];
        }

        return $noneRecord;
    }

    protected function qrcode($url, $array)
    {
        $token = $this->getTokenService()->makeToken('qrcode', array(
            'data' => array(
                'url' => $this->generateUrl($url, $array, true),
            ),
            'duration' => 3600 * 24 * 365,
        ));

        $url = $this->generateUrl('common_parse_qrcode', array('token' => $token['token']), true);

        return $this->generateUrl('common_qrcode', array('text' => $url), true);
    }

    protected function uploadMaterialAttachments($attachments, $enrollmentRecordId)
    {
        if (!empty($attachments)) {
            $user = $this->getCurrentUser();
            $orgs = $this->getOrgService()->findOrgsByIds($user['orgIds']);
            $org = reset($orgs);
            $userProfile = $this->getUserService()->getUserProfile($user['id']);
            $orgName = empty($org) ? '' : $org['name'].'+';
            $trueName = empty($userProfile['truename']) ? '' : $userProfile['truename'].'+';
            $mobile = empty($userProfile['mobile']) ? '' : $userProfile['mobile'].'+';

            foreach ($attachments as $key => $attachment) {
                $file = $this->getUploadFileService()->getFile($attachment['fileIds']);
                $filename = $orgName.$trueName.$mobile.$file['filename'];
                $file = $this->getUploadFileService()->update($attachment['fileIds'], array('name' => $filename));
                $this->getUploadFileService()->createUseFiles($attachment['fileIds'], $enrollmentRecordId, $attachment['targetType'], $attachment['type']);
            }
        }
    }

    protected function buildProjectPlanListConditions($conditions)
    {
        if (!empty($conditions['timeStatus'])) {
            if ('ongoing' === $conditions['timeStatus']) {
                $conditions = array_merge(array('endTime_GE' => time(), 'startTime_LE' => time()), $conditions);
            } elseif ('end' === $conditions['timeStatus']) {
                $conditions = array_merge(array('endTime_LE' => time()), $conditions);
            }
        }

        if (!empty($conditions['enrollment'])) {
            $conditions['enrollmentEndDate_GE'] = time();
            $conditions['enrollmentStartDate_LE'] = time();
        }
        if (isset($conditions['categoryId']) && empty($conditions['categoryId'])) {
            unset($conditions['categoryId']);
        }
        $conditions['ids'] = $this->getResourceVisibleScopeService()->findVisibleResourceIdsByResourceTypeAndUserId('projectPlan', $this->getCurrentUser()->getId());

        return $conditions;
    }

    protected function filterAdvancedOption($fields)
    {
        $materials = json_decode($fields['materials'], true);
        $fields['material2'] = empty($materials[0]) ? '' : $materials[0];
        $fields['material3'] = empty($materials[1]) ? '' : $materials[1];

        return $fields;
    }

    protected function buildAdvancedOptionCrowd($advancedOption)
    {
        $crowds = array();
        if (!empty($advancedOption)) {
            $advancedOption['orgIds'] = empty($advancedOption['orgIds']) ? array() : explode('|', $advancedOption['orgIds']);
            $advancedOption['postIds'] = empty($advancedOption['postIds']) ? array() : explode('|', $advancedOption['postIds']);
            $advancedOption['userGroupIds'] = empty($advancedOption['userGroupIds']) ? array() : explode('|', $advancedOption['userGroupIds']);

            if (!empty($advancedOption['orgIds'])) {
                $orgs = $this->getOrgService()->searchOrgs(array('orgIds' => $advancedOption['orgIds']), array(), 0, PHP_INT_MAX);
                foreach ($orgs as $org) {
                    $crowds[] = array('id' => $org['id'].'org', 'name' => $org['name'], 'attributeType' => 'org', 'itemId' => $org['id']);
                }
            }
            if (!empty($advancedOption['postIds'])) {
                $posts = $this->getPostService()->findPostsByIds($advancedOption['postIds']);
                foreach ($posts as $post) {
                    $crowds[] = array('id' => $post['id'].'post', 'name' => $post['name'], 'attributeType' => 'post', 'itemId' => $post['id']);
                }
            }

            if (!empty($advancedOption['userGroupIds'])) {
                $userGroups = $this->getUserGroupService()->searchUserGroups(array('ids' => $advancedOption['userGroupIds']), array(), 0, PHP_INT_MAX);
                foreach ($userGroups as $userGroup) {
                    $crowds[] = array('id' => $userGroup['id'].'userGroup', 'name' => $userGroup['name'], 'attributeType' => 'userGroup', 'itemId' => $userGroup['id']);
                }
            }
        }

        return json_encode($crowds);
    }

    protected function conversionTime($data, $projectPlan)
    {
        if (array_key_exists('startDateTime', $data)) {
            $data['startTime'] = empty($data['startDateTime']) ? 0 : strtotime($data['startDateTime']);
        } else {
            $data['startTime'] = $projectPlan['startTime'];
        }

        if (array_key_exists('endDateTime', $data)) {
            $data['endTime'] = empty($data['endDateTime']) ? 0 : strtotime($data['endDateTime'].' 23:59:59');
        } else {
            $data['endTime'] = $projectPlan['endTime'];
        }

        if (array_key_exists('enrollmentStartDate', $data)) {
            $data['enrollmentStartDate'] = empty($data['enrollmentStartDate']) ? 0 : strtotime($data['enrollmentStartDate']);
        } else {
            $data['enrollmentStartDate'] = $projectPlan['enrollmentStartDate'];
        }

        if (array_key_exists('enrollmentEndDate', $data)) {
            $data['enrollmentEndDate'] = empty($data['enrollmentStartDate']) ? 0 : strtotime($data['enrollmentEndDate'].' 23:59:59');
        } else {
            $data['enrollmentEndDate'] = $projectPlan['enrollmentEndDate'];
        }

        return $data;
    }

    protected function calculatePostCoursesLearnTime($userId, $courses)
    {
        foreach ($courses as $key => &$course) {
            $course['learnTime'] = $this->getTaskResultService()->sumLearnTimeByCourseIdAndUserId($key, $userId);
        }
        $learnTime = ArrayToolkit::column($courses, 'learnTime');

        return array_sum($learnTime);
    }

    protected function validateItem($fields, $type)
    {
        $message = '';

        if ('exam' == $type) {
            $testpaper = $this->getTestPaperService()->getTestPaper($fields['mediaId']);
            if (empty($testpaper) || 'published' != $testpaper['status']) {
                $message = $this->trans('exam.exam.testpaper_unpublished_or_deleted');
            }
        }

        return $message;
    }

    protected function getProjectPlanMetas()
    {
        return $this->container->get('corporatetraining.extension.manager')->getProjectPlanItems();
    }

    protected function createProjectPlanStrategy($type)
    {
        return $this->getBiz()->offsetGet('projectPlan_item_strategy_context')->createStrategy($type);
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\Impl\ProjectPlanServiceImpl
     */
    protected function getProjectPlanService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:ProjectPlanService');
    }

    /**
     * @return \ExamPlugin\Biz\Exam\Service\Impl\ExamServiceImpl
     */
    protected function getExamService()
    {
        return $this->createService('ExamPlugin:Exam:ExamService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\Impl\MemberServiceImpl
     */
    protected function getProjectPlanMemberService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:MemberService');
    }

    /**
     * @return \Biz\Course\Service\Impl\CourseServiceImpl
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return \Biz\Content\Service\Impl\FileServiceImpl
     */
    protected function getFileService()
    {
        return $this->createService('Content:FileService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineCourse\Service\Impl\TaskServiceImpl
     */
    protected function getOfflineCourseTaskService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineCourse:TaskService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineCourse\Service\Impl\MemberServiceImpl
     */
    protected function getOfflineCourseMemberService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineCourse:MemberService');
    }

    /**
     * @return \Biz\Org\Service\Impl\OrgServiceImpl
     */
    protected function getOrgService()
    {
        return $this->createService('Org:OrgService');
    }

    /**
     * @return \Biz\Course\Service\Impl\CourseSetServiceImpl
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    protected function getPostService()
    {
        return $this->createService('CorporateTrainingBundle:Post:PostService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineExam\Service\Impl\OfflineExamServiceImpl
     */
    protected function getOfflineExamService()
    {
        return $this->createservice('CorporateTrainingBundle:OfflineExam:OfflineExamService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('CorporateTrainingBundle:User:UserService');
    }

    protected function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }

    protected function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }

    protected function getPostCourseService()
    {
        return $this->createService('CorporateTrainingBundle:PostCourse:PostCourseService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\Task\Service\Impl\TaskServiceImpl
     */
    protected function getCourseTaskService()
    {
        return $this->createService('CorporateTrainingBundle:Task:TaskService');
    }

    /**
     * @return TestPaperService
     */
    protected function getTestPaperService()
    {
        return $this->createService('ExamPlugin:TestPaper:TestPaperService');
    }

    protected function getUserAttributeService()
    {
        return $this->createService('CorporateTrainingBundle:UserAttribute:UserAttributeService');
    }

    protected function getUserGroupService()
    {
        return $this->createService('CorporateTrainingBundle:UserGroup:UserGroupService');
    }

    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }

    protected function getMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return \Biz\User\Service\Impl\TokenServiceImpl
     */
    protected function getTokenService()
    {
        return $this->createService('User:TokenService');
    }

    /**
     * @return ResourceVisibleScopeService
     */
    protected function getResourceVisibleScopeService()
    {
        return $this->getBiz()->service('ResourceScope:ResourceVisibleScopeService');
    }

    /**
     * @return ResourceAccessScopeService
     */
    protected function getResourceAccessService()
    {
        return $this->createService('ResourceScope:ResourceAccessScopeService');
    }
}
