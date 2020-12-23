<?php

namespace CorporateTrainingBundle\Controller\Admin;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Controller\Admin\BaseController;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\Paginator;

class OfflineActivityCategoryController extends BaseController
{
    public function categoryAction(Request $request)
    {
        $conditions = $request->query->all();

        $group = $this->getCategoryService()->getGroupByCode('offlineActivity');

        if (empty($group)) {
            throw $this->createNotFoundException();
        }
        $conditions['groupId'] = $group['id'];

        $offlineCategoriesCount = $this->getCategoryService()->countCategories($conditions);

        $paginator = new Paginator($request, $offlineCategoriesCount, 10);

        $offlineCategories = $this->getCategoryService()->searchCategories(
            $conditions,
            array('weight' => 'ASC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $categoryIds = ArrayToolkit::column($offlineCategories, 'id');
        $conditions['status'] = 'published';
        $conditions['categoryIds'] = $categoryIds;
        $allOfflineActivity = $this->getOfflineActivityService()->searchOfflineActivities(
            $conditions,
            array(),
            0,
            PHP_INT_MAX
        );
        $groupCourses = ArrayToolkit::group($allOfflineActivity, 'categoryId');
        foreach ($offlineCategories as &$category) {
            if (!empty($groupCourses[$category['id']])) {
                $category = $this->buildCategoryData($groupCourses[$category['id']], $category);
            } else {
                $category['count'] = array('handing' => 0, 'end' => 0);
                $category['mem'] = 0;
                $category['pass'] = 0;
                $category['join'] = 0;
            }
        }

        return $this->render('admin/offline-activity/category/category.html.twig', array(
            'offlineCategorysCount' => $offlineCategoriesCount,
            'paginator' => $paginator,
            'group' => $group,
            'categories' => $offlineCategories,
        ));
    }

    public function createAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $this->getCategoryService()->createCategory($request->request->all());

            return $this->redirect($this->generateUrl('admin_offline_activity_category'));
        }

        $category = array(
            'id' => 0,
            'name' => '',
            'code' => '',
            'description' => '',
            'groupId' => (int) $request->query->get('groupId'),
            'parentId' => (int) $request->query->get('parentId', 0),
            'weight' => 0,
            'icon' => '',
        );

        return $this->render('admin/offline-activity/category/modal.html.twig', array(
            'category' => $category,
        ));
    }

    public function editAction(Request $request, $id)
    {
        $category = $this->getCategoryService()->getCategory($id);

        if (empty($category)) {
            throw $this->createNotFoundException();
        }

        if ($request->getMethod() == 'POST') {
            $category = $this->getCategoryService()->updateCategory($id, $request->request->all());

            $this->createJsonResponse(true);
        }

        return $this->render('admin/offline-activity/category/modal.html.twig', array(
            'category' => $category,
        ));
    }

    public function deleteAction(Request $request, $id)
    {
        $category = $this->getCategoryService()->getCategory($id);

        if (empty($category)) {
            throw $this->createNotFoundException();
        }

        $course = $this->getOfflineActivityService()->searchOfflineActivities(
            array('categoryId' => $id),
            array(),
            0,
            1
        );
        if (!empty($course)) {
            return $this->createJsonResponse(array('result' => false, 'message' => $this->trans('admin.offline_activity.category.message.delete_error')));
        }

        $this->getCategoryService()->deleteCategory($id);

        return $this->createJsonResponse(true);
    }

    private function buildCategoryData(array $courses, $category)
    {
        $handing = 0;
        $end = 0;
        $memjoin = 0;
        $mempass = 0;
        $courseIds = ArrayToolkit::column($courses, 'id');
        foreach ($courses as $value) {
            if ($value['endTime'] > time()) {
                ++$handing;
            } elseif ($value['endTime'] < time()) {
                ++$end;
            }
        }

        $conditions = array('offlineActivityIds' => $courseIds);
        $mems = $this->getMemberService()->searchMembers(
            $conditions,
            array(),
            0,
            PHP_INT_MAX
        );
        if (!empty($mems)) {
            foreach ($mems as $mem) {
                if ($mem['passedStatus'] == 'passed') {
                    ++$mempass;
                }
                if ($mem['attendedStatus'] == 'attended') {
                    ++$memjoin;
                }
            }
        }
        $category['mem'] = count($mems);
        $category['pass'] = $mempass;
        $category['join'] = $memjoin;
        $category['count']['handing'] = $handing;
        $category['count']['end'] = $end;

        return $category;
    }

    protected function getCategoryService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineActivity:CategoryService');
    }

    protected function getOfflineActivityService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineActivity:OfflineActivityService');
    }

    protected function getMemberService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineActivity:MemberService');
    }
}
