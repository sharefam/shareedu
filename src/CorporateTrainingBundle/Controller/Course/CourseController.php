<?php

namespace CorporateTrainingBundle\Controller\Course;

use AppBundle\Common\ArrayToolkit;
use CorporateTrainingBundle\Biz\ProjectPlan\Service\MemberService;
use CorporateTrainingBundle\Biz\ResourceScope\Service\ResourceAccessScopeService;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\Course\CourseController as BaseCourseController;

class CourseController extends BaseCourseController
{
    public function showAction(Request $request, $id, $tab = 'summary')
    {
        $tab = $this->prepareTab($tab);
        $user = $this->getCurrentUser();

        $course = $this->getCourseService()->getCourse($id);
        if (empty($course)) {
            throw $this->createNotFoundException('Course does not exist!');
        }

        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        if (empty($courseSet)) {
            throw $this->createNotFoundException('Course does not exist!');
        }

        $member = $this->getCourseMember($request, $course);
        if (!$this->canUserVisitResource($course, $member) && !$this->getCourseService()->canManageCourse($course['id'])) {
            throw $this->createAccessDeniedException('No access to this course!');
        }

        if ($this->canCourseShowRedirect($request)) {
            $lastCourseMember = $this->getMemberService()->searchMembers(
                array(
                    'userId' => $user['id'],
                    'courseSetId' => $course['courseSetId'],
                ),
                array('lastLearnTime' => 'desc'),
                0,
                1
            );
            if (!empty($lastCourseMember)) {
                $lastCourseMember = reset($lastCourseMember);

                return $this->redirect(($this->generateUrl('my_course_show', array('id' => $lastCourseMember['courseId']))));
            }
        }

        if ($this->isPluginInstalled('Discount')) {
            $discount = $this->getDiscountService()->getDiscount($courseSet['discountId']);
            if (!empty($discount)) {
                $course['discount'] = $discount;
            }
        }

        $isCourseTeacher = $this->getMemberService()->isCourseTeacher($id, $user['id']);

        $this->getCourseService()->hitCourse($id);

        $tags = $this->findCourseSetTagsByCourseSetId($course['courseSetId']);

        return $this->render(
            'course/course-show.html.twig',
            array(
                'tab' => $tab,
                'tags' => $tags,
                'course' => $course,
                'categoryTag' => $this->calculateCategoryTag($course),
                'isCourseTeacher' => $isCourseTeacher,
                'navMember' => $member,
            )
        );
    }

    protected function canUserVisitResource($course, $member = array())
    {
        if (!empty($member)) {
            return true;
        }

        $canUserAccessResource = $this->getResourceVisibleService()->canUserVisitResource('courseSet', $course['courseSetId'], $this->getCurrentUser()->getId());
        if ($canUserAccessResource) {
            return true;
        }

        return false;
    }

    protected function getCourseMember(Request $request, $course)
    {
        $previewAs = $request->query->get('previewAs');
        $user = $this->getCurrentUser();
        $member = $this->getMemberService()->getCourseMember($course['id'], $user['id']);

        /*
         * 内训版：岗位课程或培训项目或专题直接加入
         */
        $canAutoJoin = $this->getCourseService()->canUserAutoJoinCourse($user, $course['id']);
        if ($canAutoJoin) {
            $member = $this->getCourseMemberService()->becomeStudent($course['id'], $user['id']);
        }
        $member = $user['id'] ? $member : null;

        return $this->previewAsMember($previewAs, $member, $course);
    }

    public function recommendCoursesBlockAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $postCourses = $this->getPostCourseService()->findPostCoursesByPostId($user['postId']);
        $tags = array();
        foreach ($postCourses as $postCourse) {
            $tags = array_merge($tags, $this->getTagService()->findTagsByOwner(array('ownerType' => 'course-set', 'ownerId' => $postCourse['courseId'])));
        }
        $postCourseIds = ArrayToolkit::column($postCourses, 'courseId');
        $courseMembers = $this->getMemberService()->searchMembers(
            array('userId' => $user['id'], 'isLearned' => 1),
            array('id' => 'ASC'),
            0, PHP_INT_MAX
        );
        $learnedCourseIds = ArrayToolkit::column($courseMembers, 'courseId');

        $tagIds = ArrayToolkit::column($tags, 'id');

        $tagOwners = $this->getTagService()->findTagOwnerRelationsByTagIdsAndOwnerType($tagIds, 'course-set');
        $tagOwners = ArrayToolkit::index($tagOwners, 'id');

        $courseIds = ArrayToolkit::column($tagOwners, 'ownerId');

        $recommendCourseIds = array_diff($courseIds, $postCourseIds, $learnedCourseIds);

        $courses = array();

        if (!empty($recommendCourseIds)) {
            $courses = $this->getCourseSetService()->searchCourseSets(
                array('ids' => $recommendCourseIds, 'status' => 'published', 'orgCode' => '1.', 'parentId' => '0'),
                array('createdTime' => 'DESC'),
                0, 5
            );
        }

        return $this->render(
            'study-center/side-bar/recommended-course.html.twig',
            array(
            'courses' => $courses,
            )
        );
    }

    public function headerAction(Request $request, $course)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        $courses = $this->getCourseService()->findCoursesByCourseSetId($course['courseSetId']);
        $canAccess = true;
        $breadcrumbs = $this->getCategoryService()->findCategoryBreadcrumbs($courseSet['categoryId']);
        $user = $this->getCurrentUser();

        $member = $user->isLogin() ? $this->getMemberService()->getCourseMember(
            $course['id'],
            $user['id']
        ) : array();

        $postCourses = $this->getPostCourseService()->findPostCoursesByCourseId($course['id']);
        $posts = $postCourses ? ArrayToolkit::column($postCourses, 'postId') : array();

        if (in_array($user['postId'], $posts) && empty($member)) {
            $this->getMemberService()->becomeStudent($course['id'], $user['id']);
            $member = $this->getMemberService()->getCourseMember($course['id'], $user['id']);
        }

        $isUserFavorite = false;
        if ($user->isLogin()) {
            $isUserFavorite = $this->getCourseSetService()->isUserFavorite($user['id'], $course['courseSetId']);
        }

        $previewAs = $request->query->get('previewAs', null);

        $previewTasks = $this->getTaskService()->searchTasks(
            array('courseId' => $course['id'], 'type' => 'video', 'isFree' => '1'),
            array('seq' => 'ASC'),
            0,
            1
        );

        if ($courseSet['conditionalAccess']) {
            $canAccess = $this->getResourceAccessScopeService()->canUserAccessResource('courseSet', $course['courseSetId'], $user['id']);
        }

        return $this->render(
            'course/header/header-for-guest.html.twig',
            array(
                'isUserFavorite' => $isUserFavorite,
                'courseSet' => $courseSet,
                'courses' => $courses,
                'course' => $course,
                'previewTask' => empty($previewTasks) ? null : array_shift($previewTasks),
                'previewAs' => $previewAs,
                'marketingPage' => 1,
                'breadcrumbs' => $breadcrumbs,
                'canAccess' => $canAccess,
            )
        );
    }

    private function canCourseShowRedirect($request)
    {
        $host = $request->getHost();
        $referer = $request->headers->get('referer');
        if (empty($referer)) {
            return false;
        }

        $matchExpre = "/{$host}\/(my\/)?course\/(\d)+/i";
        if (preg_match($matchExpre, $referer)) {
            return false;
        }

        return true;
    }

    private function calculateCategoryTag($course)
    {
        $tasks = $this->getTaskService()->findTasksByCourseId($course['id']);
        if (empty($tasks)) {
            return;
        }
        $tag = null;
        foreach ($tasks as $task) {
            if (empty($tag) && 'video' === $task['type'] && $course['tryLookable']) {
                $activity = $this->getActivityService()->getActivity($task['activityId'], true);
                if (!empty($activity['ext']['file']) && 'cloud' === $activity['ext']['file']['storage']) {
                    $tag = 'site.badge.try_watch';
                }
            }
            //tag的权重：免费优先于试看
            if ($task['isFree']) {
                return 'site.badge.free';
            }
        }

        return $tag;
    }

    protected function getTagService()
    {
        return $this->createService('Taxonomy:TagService');
    }

    protected function getMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    protected function getPostCourseService()
    {
        return $this->createService('PostCourse:PostCourseService');
    }

    protected function getUserPostCourseService()
    {
        return $this->createService('CorporateTrainingBundle:PostCourse:UserPostCourseService');
    }

    /**
     * @return MemberService
     */
    protected function getProjectPlanMemberService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:MemberService');
    }

    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    protected function getResourceVisibleService()
    {
        return $this->createService('ResourceScope:ResourceVisibleScopeService');
    }

    /**
     * @return ResourceAccessScopeService
     */
    protected function getResourceAccessScopeService()
    {
        return $this->createService('CorporateTrainingBundle:ResourceScope:ResourceAccessScopeService');
    }
}
