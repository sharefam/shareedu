<?php

namespace CorporateTrainingBundle\Controller\Admin;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\Admin\ClassroomAdminController as BaseController;
use Symfony\Component\HttpFoundation\Request;

class ClassroomAdminController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = $request->request->all();

        $conditions['orgIds'] = $this->prepareOrgIds($conditions);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getClassroomService()->countClassrooms($conditions),
            10
        );
        $paginator->setBaseUrl($this->generateUrl('admin_classroom_ajax_list'));

        list($classroomInfo, $classroomStatusNum, $classroomCoursesNum, $priceAll, $coinPriceAll, $categories) = $this->buildClassroomListData($conditions, $paginator);

        return $this->render('admin/classroom/index.html.twig', array(
            'classroomInfo' => $classroomInfo,
            'paginator' => $paginator,
            'classroomStatusNum' => $classroomStatusNum,
            'classroomCoursesNum' => $classroomCoursesNum,
            'priceAll' => $priceAll,
            'coinPriceAll' => $coinPriceAll,
            'categories' => $categories,
            'orgIds' => implode(',', $conditions['orgIds']),
        ));
    }

    public function ajaxListAction(Request $request)
    {
        $conditions = $request->request->all();

        $conditions['orgIds'] = $this->prepareOrgIds($conditions);
        $paginator = new Paginator(
            $this->get('request'),
            $this->getClassroomService()->countClassrooms($conditions),
            10
        );

        list($classroomInfo, $classroomStatusNum, $classroomCoursesNum, $priceAll, $coinPriceAll, $categories) = $this->buildClassroomListData($conditions, $paginator);

        return $this->render('admin/classroom/list-table.html.twig', array(
            'classroomInfo' => $classroomInfo,
            'paginator' => $paginator,
            'classroomStatusNum' => $classroomStatusNum,
            'classroomCoursesNum' => $classroomCoursesNum,
            'priceAll' => $priceAll,
            'coinPriceAll' => $coinPriceAll,
            'categories' => $categories,
            'orgIds' => implode(',', $conditions['orgIds']),
        ));
    }

    protected function buildClassroomListData($conditions, $paginator)
    {
        $classroomInfo = $this->getClassroomService()->searchClassrooms(
            $conditions,
            array('createdTime' => 'desc'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $classroomIds = ArrayToolkit::column($classroomInfo, 'id');

        $coinPriceAll = array();
        $priceAll = array();
        $classroomCoursesNum = array();

        $cashRate = $this->getCashRate();

        foreach ($classroomIds as $key => $value) {
            $courses = $this->getClassroomService()->findActiveCoursesByClassroomId($value);
            $classroomCoursesNum[$value] = count($courses);

            $coinPrice = 0;
            $price = 0;

            foreach ($courses as $course) {
                $coinPrice += $course['price'] * $cashRate;
                $price += $course['price'];
            }

            $coinPriceAll[$value] = $coinPrice;
            $priceAll[$value] = $price;
        }

        $categories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($classroomInfo, 'categoryId'));

        $classroomStatusNum = $this->getDifferentClassroomNum($conditions);

        return array($classroomInfo, $classroomStatusNum, $classroomCoursesNum, $priceAll, $coinPriceAll, $categories);
    }

    protected function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }
}
