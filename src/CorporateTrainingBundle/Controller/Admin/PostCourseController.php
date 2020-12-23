<?php

namespace CorporateTrainingBundle\Controller\Admin;

use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use CorporateTrainingBundle\Biz\ResourceUsePermissionShared\Service\ResourceUsePermissionSharedService;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\Admin\BaseController;
use Topxia\Service\Common\ServiceKernel;

class PostCourseController extends BaseController
{
    public function indexAction(Request $request, $postId)
    {
        $post = $this->getPostService()->getPost($postId);
        $count = $this->getPostCourseService()->countPostCourses(array('postId' => $postId));
        $paginator = new Paginator(
            $this->get('request'),
            $count,
            20
        );
        if ($count > 0) {
            $postCourses = $this->getPostCourseService()->searchPostCourses(
                array('postId' => $postId),
                array('seq' => 'ASC'),
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
            );

            $courseSets = $this->getCourseSetService()->findCourseSetsByIds(ArrayToolkit::column($postCourses,
                'courseSetId'));
            $categories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($courseSets,
                'categoryId'));
        }

        return $this->render(
            'admin/post-course/post-course.html.twig',
            array(
                'post' => $post,
                'orgs' => empty($orgs) ? array() : $orgs,
                'courseSets' => empty($courseSets) ? array() : $courseSets,
                'categories' => empty($categories) ? array() : $categories,
                'postCourses' => empty($postCourses) ? array() : $postCourses,
                'paginator' => $paginator,
            )
        );
    }

    public function chooseCoursesAction(Request $request, $postId)
    {
        $post = $this->getPostService()->getPost($postId);

        return $this->render('admin/post-course/course-pick-modal.html.twig', array(
            'post' => $post,
        ));
    }

    public function ajaxChooseManageCoursesAction(Request $request, $postId)
    {
        $post = $this->getPostService()->getPost($postId);
        $key = $request->request->get('key', '');
        $postCourses = $this->getPostCourseService()->findPostCoursesByPostId($postId);
        $courseSetIds = ArrayToolkit::column($postCourses, 'courseSetId');

        $conditions = array(
            'excludeIds' => $courseSetIds,
            'status' => 'published',
            'title' => "%{$key}%",
            'categoryId' => $request->request->get('categoryId', ''),
        );

        $conditions['orgIds'] = $this->prepareOrgIds($conditions);

        $paginator = new Paginator(
            $request,
            $this->getCourseSetService()->countCourseSets($conditions),
            5
        );

        $paginator->setBaseUrl($this->generateUrl('admin_post_ajax_choose_manage_courses', array('postId' => $postId)));

        $courseSets = $this->getCourseSetService()->searchCourseSets(
            $conditions,
            array('updatedTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUsers($courseSets);

        return $this->render('admin/post-course/course-select-list.html.twig', array(
            'post' => $post,
            'users' => $users,
            'courseSets' => $courseSets,
            'type' => 'ajax_pagination',
            'paginator' => $paginator,
            'dataType' => 'manage',
        ));
    }

    public function ajaxChooseUsePermissionCoursesAction(Request $request, $postId)
    {
        $post = $this->getPostService()->getPost($postId);
        $key = $request->request->get('key', '');

        $conditions = array(
            'status' => 'published',
            'title' => "%{$key}%",
            'categoryId' => $request->request->get('categoryId', ''),
        );

        $recordConditions = array(
            'toUserId' => $this->getCurrentUser()->getId(),
            'resourceType' => 'courseSet',
        );
        $postCourses = $this->getPostCourseService()->findPostCoursesByPostId($postId);

        if (!empty($postCourses)) {
            $recordConditions['excludeResourceIds'] = ArrayToolkit::column($postCourses, 'courseSetId');
        }

        $records = $this->getResourceUsePermissionSharedService()->searchSharedRecords($recordConditions, array(), 0, PHP_INT_MAX, array('resourceId'));
        $conditions['ids'] = empty($records) ? array(-1) : ArrayToolkit::column($records, 'resourceId');

        $paginator = new Paginator(
            $request,
            $this->getCourseSetService()->countCourseSets($conditions),
            5
        );

        $paginator->setBaseUrl($this->generateUrl('admin_post_ajax_choose_use_permission_courses', array('postId' => $postId)));

        $courseSets = $this->getCourseSetService()->searchCourseSets(
            $conditions,
            array('updatedTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUsers($courseSets);

        return $this->render('admin/post-course/course-select-list.html.twig', array(
            'post' => $post,
            'users' => $users,
            'courseSets' => $courseSets,
            'type' => 'ajax_pagination',
            'paginator' => $paginator,
            'dataType' => 'usePermission',
        ));
    }

    public function assignCoursesAction(Request $request, $postId)
    {
        if ('POST' == $request->getMethod()) {
            $courseIds = $request->request->get('courseIds');

            $this->getPostCourseService()->batchCreatePostCourses($postId, $courseIds);

            return $this->createJsonResponse(array('success' => true));
        }
    }

    public function deleteAction(Request $request, $id)
    {
        if ('POST' == $request->getMethod()) {
            $result = $this->getPostCourseService()->deletePostCourse($id);

            return $this->createJsonResponse(array('success' => $result));
        }
    }

    public function sortAction(Request $request)
    {
        $ids = $request->request->get('ids');

        if (!empty($ids)) {
            $this->getPostCourseService()->sortPostCourses($ids);
        }

        return $this->createJsonResponse(array('success' => true, 'message' => ServiceKernel::instance()->trans('admin.post.message.sort_success')));
    }

    protected function getUsers($courseSets)
    {
        $userIds = array();
        foreach ($courseSets as &$courseSet) {
            // $tags = $this->getTagService()->findTagsByOwner(array('ownerType' => 'course', 'ownerId' => $course['id']));
            if (!empty($courseSet['tags'])) {
                $tags = $this->getTagService()->findTagsByIds($courseSet['tags']);

                $courseSet['tags'] = ArrayToolkit::column($tags, 'id');
            }
            $userIds = array_merge($userIds, array($courseSet['creator']));
        }

        $users = $this->getUserService()->findUsersByIds($userIds);
        if (!empty($users)) {
            $users = ArrayToolkit::index($users, 'id');
        }

        return $users;
    }

    private function getTagService()
    {
        return $this->createService('Taxonomy:TagService');
    }

    /**
     * @return ResourceUsePermissionSharedService
     */
    protected function getResourceUsePermissionSharedService()
    {
        return $this->createService('ResourceUsePermissionShared:ResourceUsePermissionSharedService');
    }

    protected function getPostService()
    {
        return $this->createService('CorporateTrainingBundle:Post:PostService');
    }

    protected function getPostCourseService()
    {
        return $this->createService('CorporateTrainingBundle:PostCourse:PostCourseService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    protected function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }

    protected function getOrgService()
    {
        return $this->createService('Org:OrgService');
    }
}
