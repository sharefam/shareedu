<?php

namespace CorporateTrainingBundle\Controller\Admin;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\Admin\CourseSetController as BaseController;

class CourseSetController extends BaseController
{
    public function indexAction(Request $request, $filter)
    {
        $conditions = $request->request->all();
        $conditions = $this->filterCourseSetConditions($filter, $conditions);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseSetService()->countCourseSets($conditions),
            20
        );
        $paginator->setBaseUrl($this->generateUrl('admin_course_set_ajax_list', array('filter' => $filter)));

        list($courseSets, $users, $categories, $courseSetStatusNum, $coursesCount) = $this->buildCourseSetListData($conditions, $paginator, $filter);

        $leaseCourseCount = $this->getCourseSetService()->countCourseSets(array('belong' => 'lease'));

        return $this->render(
            'admin/course-set/index.html.twig',
            array(
                'courseSets' => $courseSets,
                'users' => $users,
                'categories' => $categories,
                'paginator' => $paginator,
                'filter' => 'normal',
                'courseSetStatusNum' => $courseSetStatusNum,
                'coursesCount' => $coursesCount,
                'orgIds' => implode(',', $conditions['orgIds']),
                'leaseCourseCount' => $leaseCourseCount,
            )
        );
    }

    public function ajaxListAction(Request $request, $filter)
    {
        $conditions = $request->request->all();
        $conditions = $this->filterCourseSetConditions($filter, $conditions);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseSetService()->countCourseSets($conditions),
            20
        );
        list($courseSets, $users, $categories, $courseSetStatusNum, $coursesCount) = $this->buildCourseSetListData($conditions, $paginator, $filter);

        return $this->render(
            'admin/course-set/list-table.html.twig',
            array(
                'courseSets' => $courseSets,
                'users' => $users,
                'categories' => $categories,
                'paginator' => $paginator,
                'filter' => $filter,
                'courseSetStatusNum' => $courseSetStatusNum,
                'coursesCount' => $coursesCount,
                'orgIds' => implode(',', $conditions['orgIds']),
            )
        );
    }

    public function recommendListAction(Request $request)
    {
        $conditions = $request->request->all();
        $conditions['recommended'] = 1;

        $conditions['orgIds'] = $this->prepareOrgIds($conditions);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseSetService()->countCourseSets($conditions),
            20
        );
        $paginator->setBaseUrl($this->generateUrl('admin_course_set_recommend_ajax_list'));

        $courseSets = $this->getCourseSetService()->searchCourseSets(
            $conditions,
            'recommendedSeq',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($courseSets, 'creator'));

        $categories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($courseSets, 'categoryId'));

        return $this->render(
            'admin/course-set/course-recommend-list.html.twig',
            array(
                'courseSets' => $courseSets,
                'users' => $users,
                'paginator' => $paginator,
                'categories' => $categories,
                'orgIds' => implode(',', $conditions['orgIds']),
            )
        );
    }

    public function ajaxRecommendListAction(Request $request)
    {
        $conditions = $request->request->all();
        $conditions['recommended'] = 1;

        $conditions['orgIds'] = $this->prepareOrgIds($conditions);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseSetService()->countCourseSets($conditions),
            20
        );

        $courseSets = $this->getCourseSetService()->searchCourseSets(
            $conditions,
            'recommendedSeq',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($courseSets, 'creator'));

        $categories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($courseSets, 'categoryId'));

        return $this->render(
            'admin/course-set/course-recommend-list-table.html.twig',
            array(
                'courseSets' => $courseSets,
                'users' => $users,
                'paginator' => $paginator,
                'categories' => $categories,
                'orgIds' => implode(',', $conditions['orgIds']),
            )
        );
    }

    public function dataAction(Request $request, $filter)
    {
        $conditions = $request->request->all();

        $conditions['orgIds'] = $this->prepareOrgIds($conditions);

        $count = $this->getCourseSetService()->countCourseSets($conditions);
        $paginator = new Paginator($this->get('request'), $count, 20);
        $paginator->setBaseUrl($this->generateUrl('admin_course_set_data_ajax_list', array('filter' => $filter)));

        list($courseSets, $classrooms) = $this->buildDataListCourseSets($conditions, $paginator, $filter);

        return $this->render(
            'admin/course-set/data.html.twig',
            array(
                'courseSets' => $courseSets,
                'paginator' => $paginator,
                'filter' => $filter,
                'classrooms' => $classrooms,
                'orgIds' => implode(',', $conditions['orgIds']),
            )
        );
    }

    public function ajaxDataListAction(Request $request, $filter)
    {
        $conditions = $request->request->all();

        $conditions['orgIds'] = $this->prepareOrgIds($conditions);

        $count = $this->getCourseSetService()->countCourseSets($conditions);
        $paginator = new Paginator($this->get('request'), $count, 20);

        list($courseSets, $classrooms) = $this->buildDataListCourseSets($conditions, $paginator, $filter);

        return $this->render(
            'admin/course-set/data-tr.html.twig',
            array(
                'courseSets' => $courseSets,
                'paginator' => $paginator,
                'filter' => $filter,
                'classrooms' => $classrooms,
                'orgIds' => implode(',', $conditions['orgIds']),
            )
        );
    }

    public function setPostCorrelationAction(Request $request, $id)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($id);
        if ('POST' == $request->getMethod()) {
            $ids = $request->request->get('postNames');
            if (!empty($ids)) {
                $postIds = explode(',', $ids);
                foreach ($postIds as $postId) {
                    $count = $this->getPostCourseService()->countPostCourses(array('postId' => $postId));
                    $postCourse = array(
                        'postId' => $postId,
                        'courseId' => $courseSet['defaultCourseId'],
                        'courseSetId' => $courseSet['id'],
                        'seq' => ++$count,
                    );
                    $this->getPostCourseService()->createPostCourse($postCourse);
                }
            }

            return $this->redirect($this->generateUrl('admin_course_set'));
        }

        $postCourses = $this->getPostCourseService()->findPostCoursesByCourseId($courseSet['defaultCourseId']);
        $postIds = ArrayToolkit::column($postCourses, 'postId');
        $posts = $this->getPostService()->findPostsByIds($postIds);

        return $this->render(
            'admin/course-set/set-post-correlation-modal.html.twig',
            array(
                'courseSet' => $courseSet,
                'posts' => $posts,
            )
        );
    }

    public function postMatchAction(Request $request, $courseId)
    {
        $likeName = $request->query->get('name');
        $postCourses = $this->getPostCourseService()->findPostCoursesByCourseId($courseId);
        $ids = empty($postCourses) ? array(-1) : ArrayToolkit::column($postCourses, 'postId');
        $data = array();
        $posts = $this->getPostService()->searchPosts(
                array('excludeIds' => $ids, 'likeName' => $likeName),
                array('seq' => 'ASC'),
                0,
                100
            );

        $posts = ArrayToolkit::group($posts, 'groupId');
        foreach ($posts as $post) {
            foreach ($post as $value) {
                $data[] = array(
                    'id' => $value['id'],
                    'name' => $value['name'],
                );
            }
        }

        return $this->createJsonResponse($data);
    }

    public function deletePostCorrelationAction(Request $request, $courseId, $postId)
    {
        $postCourse = $this->getPostCourseService()->getPostCourseByPostIdAndCourseId($postId, $courseId);
        if (!empty($postCourse)) {
            $this->getPostCourseService()->deletePostCourse($postCourse['id']);
        }

        return $this->createJsonResponse(array('success' => true));
    }

    public function leaseCourseAction(Request $request)
    {
        $filter = 'lease';
        $conditions = $request->request->all();
        $conditions = $this->filterCourseSetConditions($filter, $conditions);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseSetService()->countCourseSets($conditions),
            20
        );
        $courseSets = $this->getCourseSetService()->searchCourseSets(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $categories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($courseSets, 'categoryId'));
        $courseSetStatusNum = $this->getDifferentCourseSetsNum($conditions);
        foreach ($courseSets as &$courseSet) {
            $courseSet['projectPlanNum'] = $this->getProjectPlanService()->countProjectPlanItems(array('targetType' => 'course', 'targetId' => $courseSet['defaultCourseId']));
            $courseSet['classroomNum'] = $this->getClassroomService()->countClassroomCourses(array('courseId' => $courseSet['defaultCourseId']));
        }

        return $this->render(
            'admin/course-set/lease-course.html.twig',
            array(
                'courseSets' => $courseSets,
                'categories' => $categories,
                'paginator' => $paginator,
                'filter' => $filter,
                'courseSetStatusNum' => $courseSetStatusNum,
                'orgIds' => implode(',', $conditions['orgIds']),
                'leaseCourseCount' => $this->getCourseSetService()->countCourseSets(array('belong' => 'lease')),
            )
        );
    }

    public function useRecordAction(Request $request, $courseId)
    {
        list($resources, $users, $paginator) = $this->buildClassroomUseRecord($courseId);

        return $this->render(
            'admin/course-set/course-record-modal.html.twig',
            array(
                'courseId' => $courseId,
                'resources' => $resources,
                'paginator' => $paginator,
                'users' => $users,
                'type' => 'classroom',
            )
        );
    }

    public function useRecordListAction(Request $request, $courseId, $type)
    {
        if ('projectPlan' == $type) {
            list($resources, $users, $paginator) = $this->buildProjectPlanCourseUseRecord($courseId);
        } else {
            list($resources, $users, $paginator) = $this->buildClassroomUseRecord($courseId);
        }

        return $this->render(
            'admin/course-set/course-record-list.html.twig',
            array(
                'ajax' => true,
                'resources' => $resources,
                'paginator' => $paginator,
                'users' => $users,
                'type' => $type,
            )
        );
    }

    protected function buildProjectPlanCourseUseRecord($courseId)
    {
        $items = $this->getProjectPlanService()->findProjectPlanItemsByTargetIdAndTargetType($courseId, 'course');
        $projectPlanIds = empty($items) ? array(-1) : ArrayToolkit::column($items, 'projectPlanId');
        $paginator = new Paginator(
            $this->get('request'),
            count($items),
            10
        );
        $resources = $this->getProjectPlanService()->searchProjectPlans(
            array('ids' => $projectPlanIds),
            array('id' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($resources, 'createdUserId'));

        return array($resources, $users, $paginator);
    }

    protected function buildClassroomUseRecord($courseId)
    {
        $items = $this->getClassroomService()->findClassroomIdsByCourseId($courseId);
        $classroomIds = empty($items) ? array(-1) : ArrayToolkit::column($items, 'classroomId');
        $paginator = new Paginator(
            $this->get('request'),
            count($classroomIds),
            10
        );
        $resources = $this->getClassroomService()->searchClassrooms(
            array('classroomIds' => $classroomIds),
            array('id' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($resources, 'creator'));

        return array($resources, $users, $paginator);
    }

    protected function buildDataListCourseSets($conditions, $paginator, $filter)
    {
        $courseSets = $this->getCourseSetService()->searchCourseSets(
            $conditions,
            array('id' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $courseSetIds = ArrayToolkit::column($courseSets, 'id');
        $classrooms = array();

        $courseSetIncomes = $this->getCourseSetService()->findCourseSetIncomesByCourseSetIds($courseSetIds);
        $courseSetIncomes = ArrayToolkit::index($courseSetIncomes, 'courseSetId');
        $courseIds = ArrayToolkit::column($courseSets, 'defaultCourseId');
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);

        foreach ($courseSets as $key => &$courseSet) {
            $courseSetId = $courseSet['id'];
            $defaultCourseId = $courseSet['defaultCourseId'];
            $courseCount = $this->getCourseService()->searchCourseCount(array('courseSetId' => $courseSetId));
            $isLearnedNum = empty($courses[$defaultCourseId]) ? 0 : $this->getMemberService()->countMembers(
                array('finishedTime_GT' => 0, 'courseId' => $courseSet['defaultCourseId'], 'learnedCompulsoryTaskNumGreaterThan' => $courses[$defaultCourseId]['compulsoryTaskNum'])
            );
            $taskCount = $this->getTaskService()->countTasks(array('fromCourseSetId' => $courseSetId));

            $courseSet['learnedTime'] = $this->getTaskService()->sumCourseSetLearnedTimeByCourseSetId($courseSetId);
            $courseSet['learnedTime'] = round($courseSet['learnedTime'] / 60);
            if (!empty($courseSetIncomes[$courseSetId])) {
                $courseSet['income'] = $courseSetIncomes[$courseSetId]['income'];
            } else {
                $courseSet['income'] = 0;
            }
            $courseSet['isLearnedNum'] = $isLearnedNum;
            $courseSet['taskCount'] = $taskCount;
            $courseSet['courseCount'] = $courseCount;
        }

        return array($courseSets, $classrooms);
    }

    protected function buildCourseSetListData($conditions, $paginator, $filter)
    {
        $courseSets = $this->getCourseSetService()->searchCourseSets(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        list($courseSets, $coursesCount) = $this->findRelatedOptions($filter, $courseSets);

        $categories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($courseSets, 'categoryId'));
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($courseSets, 'creator'));
        $courseSetStatusNum = $this->getDifferentCourseSetsNum($conditions);
        foreach ($courseSets as &$courseSet) {
            $courseSet['projectPlanNum'] = $this->getProjectPlanService()->countProjectPlanItems(array('targetType' => 'course', 'targetId' => $courseSet['defaultCourseId']));
            $courseSet['classroomNum'] = $this->getClassroomService()->countClassroomCourses(array('courseId' => $courseSet['defaultCourseId']));
        }

        return array($courseSets, $users, $categories, $courseSetStatusNum, $coursesCount);
    }

    /**
     * @return \CorporateTrainingBundle\Biz\Post\Service\Impl\PostServiceImpl
     */
    protected function getPostService()
    {
        return $this->createService('Post:PostService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\PostCourse\Service\Impl\PostCourseServiceImpl
     */
    protected function getPostCourseService()
    {
        return $this->createService('PostCourse:PostCourseService');
    }
}
