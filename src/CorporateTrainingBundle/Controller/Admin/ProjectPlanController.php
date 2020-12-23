<?php

namespace CorporateTrainingBundle\Controller\Admin;

use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\Admin\BaseController;
use Biz\Org\Service\OrgService;
use Biz\Course\Service\CourseSetService;
use CorporateTrainingBundle\Biz\ProjectPlan\Service\MemberService;
use CorporateTrainingBundle\Biz\ProjectPlan\Service\ProjectPlanService;
use AppBundle\Common\Paginator;

class ProjectPlanController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = $request->request->all();
        $conditions = $this->prepareManageListSearchConditions($conditions);

        $projectPlanCount = $this->getProjectPlanService()->countProjectPlans($conditions);
        $paginator = new Paginator(
            $request,
            $projectPlanCount,
            20
        );
        $paginator->setBaseUrl($this->generateUrl('admin_project_plan_manage_ajax_list'));

        $projectPlans = $this->buildProjectPlanListData($conditions, $paginator);

        return $this->render('admin/project-plan/index.html.twig',
        array(
            'projectPlans' => $projectPlans,
            'paginator' => $paginator,
            'orgIds' => implode(',', $conditions['orgIds']),
            )
        );
    }

    public function ajaxListAction(Request $request)
    {
        $conditions = $request->request->all();
        $conditions = $this->prepareManageListSearchConditions($conditions);

        $projectPlanCount = $this->getProjectPlanService()->countProjectPlans($conditions);
        $paginator = new Paginator(
            $request,
            $projectPlanCount,
            20
        );
        $projectPlans = $this->buildProjectPlanListData($conditions, $paginator);

        return $this->render('admin/project-plan/list-tr.html.twig',
            array(
                'projectPlans' => $projectPlans,
                'paginator' => $paginator,
                'orgIds' => implode(',', $conditions['orgIds']),
            )
        );
    }

    public function categoryAction(Request $request)
    {
        $conditions = $request->query->all();

        $group = $this->getCategoryService()->getGroupByCode('projectPlan');

        if (empty($group)) {
            throw $this->createNotFoundException();
        }
        $conditions['groupId'] = $group['id'];

        $projectPlanCategoryNum = $this->getCategoryService()->countCategories($conditions);

        $paginator = new Paginator($request, $projectPlanCategoryNum, 10);
        $categories = $this->getCategoryService()->searchCategories(
            $conditions,
            array('weight' => 'ASC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('admin/project-plan/category/index.html.twig', array(
            'paginator' => $paginator,
            'group' => $group,
            'categories' => $categories,
            'categoryNum' => $projectPlanCategoryNum,
        ));
    }

    public function createCategoryAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $category = $this->getCategoryService()->createCategory($request->request->all());

            return $this->redirect($this->generateUrl('admin_project_plan_category_manage'));
        }

        $category = array(
            'id' => 0,
            'name' => '',
            'code' => '',
            'description' => '',
            'groupId' => (int) $request->query->get('groupId'),
            'parentId' => 0,
            'weight' => 0,
            'icon' => '',
        );

        return $this->render('admin/project-plan/category/modal.html.twig', array(
            'category' => $category,
        ));
    }

    public function editCategoryAction(Request $request, $id)
    {
        $category = $this->getCategoryService()->getCategory($id);

        if (empty($category)) {
            throw $this->createNotFoundException();
        }

        if ('POST' == $request->getMethod()) {
            $category = $this->getCategoryService()->updateCategory($id, $request->request->all());

            return $this->renderTbody($category['groupId']);
        }

        return $this->render('admin/project-plan/category/modal.html.twig', array(
            'category' => $category,
        ));
    }

    public function deleteCategoryAction(Request $request, $id)
    {
        $category = $this->getCategoryService()->getCategory($id);

        if (empty($category)) {
            throw $this->createNotFoundException();
        }

        $projectPlan = $this->getProjectPlanService()->searchProjectPlans(
            array('categoryId' => $id),
            array(),
            0,
            1
        );
        if (!empty($projectPlan)) {
            return $this->createJsonResponse(false);
        }

        $this->getCategoryService()->deleteCategory($id);

        return $this->createJsonResponse(true);
    }

    protected function renderTbody($groupId)
    {
        $group = $this->getCategoryService()->getGroup($groupId);
        $categories = $this->getCategoryService()->getCategoryStructureTree($groupId);

        return $this->render('admin/project-plan/category/tbody.html.twig', array(
            'categories' => $categories,
            'group' => $group,
        ));
    }

    protected function prepareManageListSearchConditions($conditions)
    {
        $conditions['orgIds'] = $this->prepareOrgIds($conditions);

        if (!empty($conditions['status']) && 'all' === $conditions['status']) {
            unset($conditions['status']);
        }

        return $conditions;
    }

    private function getAssignmentMemberNum($projectPlanId)
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

        return count($userIds);
    }

    protected function buildProjectPlanListData($conditions, $paginator)
    {
        $projectPlans = $this->getProjectPlanService()->searchProjectPlans(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        foreach ($projectPlans as &$projectPlan) {
            $projectPlan['memberNum'] = $this->getAssignmentMemberNum($projectPlan['id']);
            $projectPlan['itemNum'] = $this->getProjectPlanService()->countProjectPlanItems(array('projectPlanId' => $projectPlan['id']));
        }

        return $projectPlans;
    }

    /**
     * @return ProjectPlanService
     */
    protected function getProjectPlanService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:ProjectPlanService');
    }

    /**
     * @return MemberService
     */
    protected function getProjectPlanMemberService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:MemberService');
    }

    /**
     * @return OrgService
     */
    protected function getOrgService()
    {
        return $this->createService('Org:OrgService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    protected function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }
}
