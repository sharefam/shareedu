<?php

namespace CorporateTrainingBundle\Controller\Admin\Data;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\Admin\BaseController;
use Biz\Course\Service\ReportService;
use CorporateTrainingBundle\Biz\Course\Service\CourseService;
use CorporateTrainingBundle\Biz\Course\Service\CourseSetService;
use CorporateTrainingBundle\Biz\Course\Service\MemberService;
use CorporateTrainingBundle\Biz\Task\Service\TaskResultService;
use CorporateTrainingBundle\Biz\Task\Service\TaskService;
use CorporateTrainingBundle\Biz\Taxonomy\Service\CategoryService;
use CorporateTrainingBundle\Common\DateToolkit;
use SurveyPlugin\Biz\Survey\Service\SurveyService;
use Symfony\Component\HttpFoundation\Request;

class CourseDataController extends BaseController
{
    public function overviewAction(Request $request, $type)
    {
        $view = 'CorporateTrainingBundle::admin/data/course/overview/course-num.html.twig';

        if ('student' == $type) {
            $view = 'CorporateTrainingBundle::admin/data/course/overview/course-student-num.html.twig';
        }

        return $this->render($view);
    }

    public function overviewDataAction(Request $request, $type)
    {
        $conditions = $request->query->all();
        if (!empty($conditions['year'])) {
            $startTime = $conditions['year'].'-01-01';
            $endTime = $conditions['year'].'-12-31';
            $conditions['startTime'] = strtotime($startTime);
            $conditions['endTime'] = strtotime($endTime.' 23:59:59');
        }

        $conditions = $this->fillOrgCode($conditions);
        $conditions['excludeStatus'] = array('draft');
        $courseSets = $this->getCourseSetService()->searchCourseSets(
            $conditions,
            array('id' => 'ASC'),
            0,
            PHP_INT_MAX
        );

        $courseSetsGroupByCategoryId = ArrayToolkit::group($courseSets, 'categoryId');
        $categories = $this->findCategories(ArrayToolkit::column($courseSets, 'categoryId'));
        $results = $this->caculateCategoriesDepth($categories);
        $categories = $results['categories'];
        $depth = $results['depth'];
        $categoriesGroupByParentId = ArrayToolkit::group($categories, 'parentId');

        if ('course' == $type) {
            $categories = $this->countCategoriesCourseNum($categories, $categoriesGroupByParentId, $courseSetsGroupByCategoryId);
            $categoriesData = $this->combineCategoryCourseData($categories, $categoriesGroupByParentId, $depth);
        } else {
            $categories = $this->countCategoriesCourseStudentNum($categories, $categoriesGroupByParentId, $courseSetsGroupByCategoryId);
            $categoriesData = $this->combineCategoryCourseData($categories, $courseSetsGroupByCategoryId, $depth);
        }

        return $this->createJsonResponse($categoriesData);
    }

    protected function findCategories($categoryIds)
    {
        $categories = $this->getCategoryService()->findCategoriesByIds($categoryIds);

        $parentIds = $this->findParentCategoryIds($categories, array());
        $parentCategories = $this->getCategoryService()->findCategoriesByIds($parentIds);

        $categories = array_merge($categories, $parentCategories);

        return ArrayToolkit::index($categories, 'id');
    }

    protected function findParentCategoryIds($categories, $resultId)
    {
        $parentIds = ArrayToolkit::column($categories, 'parentId');
        if (!empty($parentIds)) {
            $resultId = array_merge($resultId, $parentIds);
            $parentCategories = $this->getCategoryService()->findCategoriesByIds($parentIds);
            $resultId = $this->findParentCategoryIds($parentCategories, $resultId);
        }

        return $resultId;
    }

    public function detailAction(Request $request)
    {
        $conditions = $request->query->all();
        $conditions = $this->fillOrgCode($conditions);
        $conditions['excludeStatus'] = array('draft');
        $createdTimeData = array();
        if (empty($conditions['createdTime'])) {
            list($startDateTime, $endDateTime) = DateToolkit::generateStartDateAndEndDate('year');
            $createdTimeData['startTime'] = strtotime($startDateTime);
            $createdTimeData['endTime'] = strtotime($endDateTime.' 23:59:59');
        } else {
            $date = explode('-', $conditions['createdTime']);
            $createdTimeData['startTime'] = strtotime($date[0]);
            $createdTimeData['endTime'] = strtotime($date[1].' 23:59:59');
        }
        $conditions = array_merge($conditions, $createdTimeData);

        $courseSetNum = $this->getCourseSetService()->countCourseSets($conditions);

        $paginator = new Paginator(
            $request,
            $courseSetNum,
            10
        );

        $courseSets = $this->getCourseSetService()->searchCourseSets(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        foreach ($courseSets as &$courseSet) {
            $conditions = array(
                'courseSetId' => $courseSet['id'],
                'role' => 'student',
            );
            $userIds = $this->getMemberService()->searchMembers(
                $conditions,
                array('id' => 'ASC'),
                0,
                PHP_INT_MAX,
                array('userId')
            );

            $totalLearnTime = 0;
            $courseProgress = 0;
            $compulsoryFinishedTaskNumGroupByUserIds = array();
            $userIds = ArrayToolkit::column($userIds, 'userId');
            if (!empty($userIds)) {
                $totalLearnTime = $this->getTaskResultService()->sumCompulsoryTasksLearnTimeByCourseIdAndUserIds($courseSet['defaultCourseId'], $userIds);
                $compulsoryFinishedTaskNumGroupByUserIds = $this->getTaskResultService()->countFinishedCompulsoryTasksByUserIdsAndCourseId($userIds, $courseSet['defaultCourseId']);
                $compulsoryFinishedTaskNumGroupByUserIds = ArrayToolkit::index($compulsoryFinishedTaskNumGroupByUserIds, 'userId');
            }
            $compulsoryCourseTaskNum = $this->getTaskService()->countTasks(array('courseId' => $courseSet['defaultCourseId'], 'isOptional' => 0));
            if (0 != $compulsoryCourseTaskNum) {
                foreach ($compulsoryFinishedTaskNumGroupByUserIds as $compulsoryFinishedTaskNumGroupByUserId) {
                    $compulsoryFinishedTaskNum = $compulsoryFinishedTaskNumGroupByUserId['finishTaskNum'];
                    $courseProgress += empty($compulsoryCourseTaskNum) ? 0 : $compulsoryFinishedTaskNum / $compulsoryCourseTaskNum;
                }
            }
            $courseSet['totalLearnTime'] = $totalLearnTime;
            $courseSet['courseProgress'] = $courseProgress;
        }

        $categories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($courseSets, 'categoryId'));
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($courseSets, 'creator'));

        return $this->render(
            'CorporateTrainingBundle::admin/data/course/detail.html.twig',
            array(
                'courseSets' => $courseSets,
                'categories' => $categories,
                'users' => $users,
                'paginator' => $paginator,
                'createdTimeData' => $createdTimeData,
            )
        );
    }

    public function courseDataAction(Request $request, $courseSetId, $courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);

        $summary = $this->getReportService()->summary($course['id']);

        return $this->render(
            'CorporateTrainingBundle::admin/data/course/detail-show.html.twig',
            array(
                'summary' => $summary,
                'courseSet' => $courseSet,
                'course' => $course,
            )
        );
    }

    protected function caculateCategoriesDepth($categories)
    {
        $categoryGroup = $this->getCategoryService()->getGroupByCode('course');
        $levelCategoryIds = array();
        $parentIds = array(0);
        for ($i = 0; $i <= ($categoryGroup['depth'] - 1); ++$i) {
            if ($i > 0 && empty($parentIds)) {
                break;
            }
            $levelCategories = $this->getCategoryService()->searchCategories(
                array(
                    'parentIds' => $parentIds,
                    'groupId' => $categoryGroup['id'],
                ),
                array('id' => 'ASC'),
                0,
                PHP_INT_MAX
            );
            $levelCategoryIds[$i] = $parentIds = ArrayToolkit::column($levelCategories, 'id');
        }

        $maxDepth = 0;
        $minDepth = ($categoryGroup['depth'] - 1);
        foreach ($categories as &$category) {
            for ($i = 0; $i <= ($categoryGroup['depth'] - 1); ++$i) {
                if (!isset($levelCategoryIds[$i])) {
                    break;
                }
                if (in_array($category['id'], $levelCategoryIds[$i])) {
                    $category['depth'] = $i;

                    if ($i > $maxDepth) {
                        $maxDepth = $i;
                    }

                    if ($i < $minDepth) {
                        $minDepth = $i;
                    }
                }
            }
        }

        return array(
            'categories' => $categories,
            'minDepth' => $minDepth,
            'depth' => $maxDepth,
        );
    }

    protected function countCategoriesCourseNum($categories, $categoriesGroupByParentId, $courseSetsGroupByCategoryId)
    {
        $categoryGroup = $this->getCategoryService()->getGroupByCode('course');
        for ($i = ($categoryGroup['depth'] - 1); $i >= 0; --$i) {
            foreach ($categories as &$category) {
                if ($category['depth'] == $i) {
                    $category['courseNum'] = empty($courseSetsGroupByCategoryId[$category['id']]) ? 0 : count($courseSetsGroupByCategoryId[$category['id']]);
                    $category['allCourseNum'] = empty($courseSetsGroupByCategoryId[$category['id']]) ? 0 : count($courseSetsGroupByCategoryId[$category['id']]);
                    $childrenCategories = empty($categoriesGroupByParentId[$category['id']]) ? array() : $categoriesGroupByParentId[$category['id']];

                    if (!empty($childrenCategories)) {
                        foreach ($childrenCategories as $childrenCategory) {
                            $category['allCourseNum'] += empty($categories[$childrenCategory['id']]['allCourseNum']) ? 0 : $categories[$childrenCategory['id']]['allCourseNum'];
                        }
                    }
                }
            }
        }

        return $categories;
    }

    protected function countCategoriesCourseStudentNum($categories, $categoriesGroupByParentId, $courseSetsGroupByCategoryId)
    {
        $categoryGroup = $this->getCategoryService()->getGroupByCode('course');
        for ($i = ($categoryGroup['depth'] - 1); $i >= 0; --$i) {
            foreach ($categories as &$category) {
                if ($category['depth'] == $i) {
                    $studentNum = 0;

                    if (!empty($courseSetsGroupByCategoryId[$category['id']])) {
                        foreach ($courseSetsGroupByCategoryId[$category['id']] as $courseSet) {
                            $studentNum += $courseSet['studentNum'];
                        }
                    }

                    $category['studentNum'] = $studentNum;
                    $category['allStudentNum'] = $studentNum;
                    $childrenCategories = empty($categoriesGroupByParentId[$category['id']]) ? array() : $categoriesGroupByParentId[$category['id']];

                    if (!empty($childrenCategories)) {
                        foreach ($childrenCategories as $childrenCategory) {
                            $category['allStudentNum'] += empty($categories[$childrenCategory['id']]['allStudentNum']) ? 0 : $categories[$childrenCategory['id']]['allStudentNum'];
                        }
                    }
                }
            }
        }

        return $categories;
    }

    protected function combineCategoryCourseData($categories, $categoriesGroupByParentId, $depth)
    {
        $categoryGroup = $this->getCategoryService()->getGroupByCode('course');
        $categoriesData = array();
        for ($i = 0; $i <= ($categoryGroup['depth'] - 1); ++$i) {
            $categoriesData['data'][$i] = array(
                'name' => $this->trans('admin.data_center.online_course.course_num'),
                'level' => array(
                    'code' => ($i + 1),
                ),
                'data' => array(),
            );
        }

        foreach ($categories as $category) {
            if (0 == $category['depth']) {
                $categoriesData = $this->joinCategoryData($categoriesData, $category, $depth);

                if (!empty($categoriesGroupByParentId[$category['id']])) {
                    $categoriesData = $this->joinChildrenCategoryData($categoriesData, $categories, $categoriesGroupByParentId, $categoriesGroupByParentId[$category['id']], $depth);
                }
            }
        }

        $categoriesData['names'] = ArrayToolkit::column($categories, 'name');

        return $categoriesData;
    }

    protected function joinCategoryData($categoriesData, $category, $depth)
    {
        $data = array();
        for ($i = $category['depth']; $i <= $depth; ++$i) {
            if ($category['depth'] == $i) {
                $data = array(
                    'name' => $category['name'],
                    'value' => $category['allCourseNum'],
                );
            }

            if ($category['depth'] != $i) {
                $data = array(
                    'name' => $category['name'],
                    'value' => $category['courseNum'],
                );
            }
            array_push($categoriesData['data'][($i)]['data'], $data);
        }

        return $categoriesData;
    }

    protected function joinChildrenCategoryData($categoriesData, $categories, $categoriesGroupByParentId, $childrenCategories, $depth)
    {
        foreach ($childrenCategories as $category) {
            $categoriesData = $this->joinCategoryData($categoriesData, $categories[$category['id']], $depth);

            if (!empty($categoriesGroupByParentId[$category['id']])) {
                $categoriesData = $this->joinChildrenCategoryData($categoriesData, $categories, $categoriesGroupByParentId, $categoriesGroupByParentId[$category['id']], $depth);
            }
        }

        return $categoriesData;
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('CorporateTrainingBundle:Course:CourseSetService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('CorporateTrainingBundle:Course:CourseService');
    }

    /**
     * @return MemberService
     */
    protected function getMemberService()
    {
        return $this->createService('CorporateTrainingBundle:Course:MemberService');
    }

    /**
     * @return CategoryService
     */
    protected function getCategoryService()
    {
        return $this->createService('CorporateTrainingBundle:Taxonomy:CategoryService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('CorporateTrainingBundle:Task:TaskService');
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
    {
        return $this->createService('CorporateTrainingBundle:Task:TaskResultService');
    }

    /**
     * @return ReportService
     */
    protected function getReportService()
    {
        return $this->createService('Course:ReportService');
    }

    /**
     * @return SurveyService
     */
    protected function getSurveyService()
    {
        return $this->createService('SurveyPlugin:Survey:SurveyService');
    }
}
