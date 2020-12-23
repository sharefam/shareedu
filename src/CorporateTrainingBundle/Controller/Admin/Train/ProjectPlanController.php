<?php

namespace CorporateTrainingBundle\Controller\Admin\Train;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\BaseController;
use CorporateTrainingBundle\Biz\ResourceScope\Service\ResourceVisibleScopeService;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;

class ProjectPlanController extends BaseController
{
    public function createAction(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$this->getProjectPlanService()->hasManageProjectPlanPermission()) {
            return $this->createMessageResponse('error', 'project_plan.message.can_not_create_message');
        }

        if ('POST' == $request->getMethod()) {
            $fields = $request->request->all();
            $org = $this->getOrgService()->getOrgByOrgCode($fields['orgCode']);
            $fields['orgId'] = empty($org) ? 1 : $org['id'];
            $fields['requireEnrollment'] = 1;

            $projectPlan = $this->getProjectPlanService()->createProjectPlan($fields);

            $this->getResourceVisibleScopeService()->setResourceVisibleScope($projectPlan['id'], 'projectPlan', array('showable' => 1, 'publishOrg' => $fields['orgId']));

            $this->setFlashMessage('success', 'project_plan.message.update_success_message');

            return $this->redirect($this->generateUrl('project_plan_manage_base', array('id' => $projectPlan['id'])));
        }

        return $this->render('project-plan/create.html.twig', array(
                'user' => $user,
            )
        );
    }

    public function manageAction(Request $request)
    {
        if (!$this->getProjectPlanService()->hasManageProjectPlanPermission()) {
            return $this->createMessageResponse('error', 'project_plan.message.can_not_manage_message');
        }

        $conditions = $request->request->all();
        $conditions = $this->prepareManageListSearchConditions($conditions);

        $projectPlanCount = $this->getProjectPlanService()->countProjectPlans($conditions);
        $paginator = new Paginator(
            $request,
            $projectPlanCount,
            20
        );

        $projectPlans = $this->getProjectPlanService()->searchProjectPlans(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        foreach ($projectPlans as &$projectPlan) {
            $projectPlan['memberNum'] = $this->getProjectPlanMemberNum($projectPlan['id'], $conditions['orgIds']);
            $projectPlan['itemNum'] = $this->getProjectPlanCourseNum($projectPlan['id'], $conditions['orgIds']);
        }

        $orgIds = ArrayToolkit::column($projectPlans, 'orgId');
        $orgs = $this->getOrgService()->findOrgsByIds($orgIds);
        $orgs = ArrayToolkit::index($orgs, 'id');

        return $this->render(
            'CorporateTrainingBundle::admin/train/project-plan-list.html.twig',
            array(
                'projectPlans' => $projectPlans,
                'orgs' => $orgs,
                'orgIds' => implode(',', $conditions['orgIds']),
                'paginator' => $paginator,
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

        if ($projectPlan['startTime']) {
            $this->getProjectPlanService()->publishProjectPlan($id);
            $success = true;
            $message = '';
        } else {
            $success = false;
            $message = ServiceKernel::instance()->trans('project_plan.publish.message.data_empty');
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

    private function getProjectPlanMemberNum($projectPlanId, $orgIds)
    {
        $projectPlanMembers = $this->getProjectPlanMemberService()->searchProjectPlanMembers(
            array('projectPlanId' => $projectPlanId),
            array(),
            0,
            PHP_INT_MAX
        );

        if (empty($projectPlanMembers)) {
            return 0;
        }

        $userIds = ArrayToolkit::column($projectPlanMembers, 'userId');
        $conditions = array(
            'userIds' => $userIds,
            'orgIds' => $orgIds,
        );

        return $this->getUserService()->countUsers($conditions);
    }

    private function getProjectPlanCourseNum($projectPlanId, $orgIds)
    {
        $courseItems = $this->getProjectPlanService()->findProjectPlanItemsByProjectPlanIdAndTargetType($projectPlanId, 'course');

        if (empty($courseItems)) {
            return 0;
        }

        $courseIds = ArrayToolkit::column($courseItems, 'targetId');

        $conditions = array(
            'defaultCourseIds' => $courseIds,
            'orgIds' => $orgIds,
        );

        return $this->getCourseSetService()->countCourseSets($conditions);
    }

    protected function prepareManageListSearchConditions($conditions)
    {
        if (!isset($conditions['orgIds']) || empty($conditions['orgIds'])) {
            $currentUser = $this->getCurrentUser();
            $orgCodes = $currentUser['orgCodes'];
            $orgIds = ArrayToolkit::column($this->getOrgService()->findOrgsByPrefixOrgCodes($orgCodes), 'id');
        } else {
            $orgIds = explode(',', $conditions['orgIds']);
        }

        $conditions['orgIds'] = $orgIds;

        if (!empty($conditions['startDateTime'])) {
            $conditions['startTime_GE'] = strtotime($conditions['startDateTime'].' 23:59:59');
            unset($conditions['startDateTime']);
        }

        if (!empty($conditions['endDateTime'])) {
            $conditions['endTime_LE'] = strtotime($conditions['endDateTime'].' 23:59:59');
            unset($conditions['endDateTime']);
        }

        if (!empty($conditions['status']) && 'all' === $conditions['status']) {
            unset($conditions['status']);
        }

        return $conditions;
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\Impl\ProjectPlanServiceImpl
     */
    protected function getProjectPlanService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:ProjectPlanService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\Impl\MemberServiceImpl
     */
    protected function getProjectPlanMemberService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:MemberService');
    }

    /**
     * @return \Biz\Course\Service\Impl\CourseSetServiceImpl
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return \Biz\Org\Service\Impl\OrgServiceImpl
     */
    protected function getOrgService()
    {
        return $this->createService('Org:OrgService');
    }

    /**
     * @return ResourceVisibleScopeService
     */
    protected function getResourceVisibleScopeService()
    {
        return $this->getBiz()->service('ResourceScope:ResourceVisibleScopeService');
    }
}
