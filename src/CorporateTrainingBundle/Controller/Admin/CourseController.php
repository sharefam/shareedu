<?php

namespace CorporateTrainingBundle\Controller\Admin;

use AppBundle\Controller\Admin\CourseController as BaseController;
use Symfony\Component\HttpFoundation\Request;

class CourseController extends BaseController
{
    public function categoryAction(Request $request)
    {
        return $this->forward('AppBundle:Admin/Category:embed', array(
            'group' => 'course',
            'menu' => 'admin_course_category_manage',
            'layout' => 'admin/layout.html.twig',
        ));
    }
}