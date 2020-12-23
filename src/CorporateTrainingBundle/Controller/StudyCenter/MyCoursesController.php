<?php

namespace CorporateTrainingBundle\Controller\StudyCenter;

use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use CorporateTrainingBundle\Controller\StudyCenter\StudyCenterBaseController as BaseController;

class MyCoursesController extends BaseController
{
    public function learningAction(Request $request, $category)
    {
        list($conditions, $categoryArray, $category, $categoryArrayDescription, $categoryParent, $tags) = $this->prepareCourseSetConditions($request, $category, 'learning');
        list($courseSets, $paginator, $isFilterSpread) = $this->buildCourseSetData($request, $conditions);

        return $this->render(
            'study-center/my-courses/learning.html.twig',
            array(
                'tags' => $tags,
                'courseSets' => $courseSets,
                'paginator' => $paginator,
                'category' => $category,
                'categoryArray' => $categoryArray,
                'categoryArrayDescription' => $categoryArrayDescription,
                'categoryParent' => $categoryParent,
                'isFilterSpread' => $isFilterSpread,
            )
        );
    }

    public function learnedAction(Request $request, $category)
    {
        list($conditions, $categoryArray, $category, $categoryArrayDescription, $categoryParent, $tags) = $this->prepareCourseSetConditions($request, $category, 'learned');
        list($courseSets, $paginator, $isFilterSpread) = $this->buildCourseSetData($request, $conditions);

        return $this->render(
            'study-center/my-courses/learned.html.twig',
            array(
                'tags' => $tags,
                'courseSets' => $courseSets,
                'paginator' => $paginator,
                'category' => $category,
                'categoryArray' => $categoryArray,
                'categoryArrayDescription' => $categoryArrayDescription,
                'categoryParent' => $categoryParent,
                'isFilterSpread' => $isFilterSpread,
            )
        );
    }

    protected function buildCourseSetData($request, $conditions)
    {
        $isFilterSpread = isset($conditions['isFilterSpread']) ? $conditions['isFilterSpread'] : 'false';

        $paginator = new Paginator(
            $request,
            $this->getMemberService()->countMembers($conditions),
            9
        );

        $courseMembers = $this->getMemberService()->searchMembers(
            $conditions,
            array('lastLearnTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $courseSetIds = ArrayToolkit::column($courseMembers, 'courseSetId');

        $unSortCourseSets = $this->getCourseSetService()->findCourseSetsByIds($courseSetIds);

        $courseSets = array();
        foreach ($courseSetIds as $courseSetId) {
            foreach ($unSortCourseSets as $id => $courseSet) {
                if ($courseSetId == $id) {
                    array_push($courseSets, $courseSet);
                }
            }
        }

        return array($courseSets, $paginator, $isFilterSpread);
    }

    protected function prepareCourseSetConditions($request, $category, $type = '')
    {
        $user = $this->getCurrentUser();
        if ('learned' == $type) {
            $courses = $this->getCourseService()->findUserLearnedCourses(
                $user['id'],
                0,
                PHP_INT_MAX
            );
        } else {
            $courses = $this->getCourseService()->findUserLearningCourses(
                $user['id'],
                0,
                PHP_INT_MAX
            );
        }
        $conditions = $request->query->all();

        list($conditions, $tags) = $this->mergeConditionsByTag($conditions, 'course-set');

        list($conditions, $categoryArray, $category, $categoryArrayDescription, $categoryParent) = $this->mergeConditionsByCategory(
            $conditions,
            $category
        );
        $conditions['ids'] = empty($courses) ? array(-1) : ArrayToolkit::column($courses, 'courseSetId');

        $courseSets = $this->getCourseSetService()->searchCourseSets(
            $conditions,
            array(),
            0,
            PHP_INT_MAX
        );
        $courseSetIds = empty($courseSets) ? array(-1) : ArrayToolkit::column($courseSets, 'id');

        if (isset($conditions['ids'])) {
            unset($conditions['ids']);
        }
        $conditions['courseSetIds'] = $courseSetIds;
        $conditions['userId'] = $user['id'];

        return array($conditions, $categoryArray, $category, $categoryArrayDescription, $categoryParent, $tags);
    }
}
