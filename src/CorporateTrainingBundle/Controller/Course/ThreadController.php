<?php

namespace CorporateTrainingBundle\Controller\Course;

use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\Course\ThreadController as BaseThreadController;

class ThreadController extends BaseThreadController
{
    public function showAction(Request $request, $courseId, $threadId)
    {
        //内训版：访问岗位课程话题时，直接加入课程
        $user = $this->getCurrentUser();
        $postCourses = $this->getPostCourseService()->findPostCoursesByPostId($user['postId']);
        $member = $this->getMemberService()->getCourseMember($courseId, $user['id']);

        if (in_array($courseId, ArrayToolkit::column($postCourses, 'courseId')) && empty($member)) {
            $this->getMemberService()->becomeStudent($courseId, $user['id']);
        }

        list($course, $member, $response) = $this->tryBuildCourseLayoutData($request, $courseId);

        if (!empty($response)) {
            return $response;
        }

        $user = $this->getCurrentUser();

        $isMemberNonExpired = true;
        if ($member && !$this->getMemberService()->isMemberNonExpired($course, $member)) {
            $isMemberNonExpired = false;
        } else {
            $isMemberNonExpired = true;
        }

        $thread = $this->getThreadService()->getThread($course['id'], $threadId);

        if (empty($thread)) {
            throw $this->createNotFoundException('The topic does not exist or has been deleted.');
        }

        $paginator = new Paginator(
            $request,
            $this->getThreadService()->getThreadPostCount($course['id'], $thread['id']),
            30
        );

        $posts = $this->getThreadService()->findThreadPosts(
            $thread['courseId'],
            $thread['id'],
            'default',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        if ('question' == $thread['type'] && 1 == $paginator->getCurrentPage()) {
            $elitePosts = $this->getThreadService()->findThreadElitePosts($thread['courseId'], $thread['id'], 0, 10);
        } else {
            $elitePosts = array();
        }

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($posts, 'userId'));

        $this->getThreadService()->hitThread($courseId, $threadId);

        //TODO 先注释掉旧的判断逻辑
        // $isManager = $this->getCourseService()->hasCourseManagerRole($course['id'], 'admin_course_thread');
        $isManager = $this->getCourseService()->canManageCourse($course['id']);

        $task = $this->getTaskService()->getTask($thread['taskId']);

        return $this->render('course/thread/show.html.twig', array(
            'course' => $course,
            'member' => $member,
            'task' => $task,
            'thread' => $thread,
            'author' => $this->getUserService()->getUser($thread['userId']),
            'posts' => $posts,
            'elitePosts' => $elitePosts,
            'users' => $users,
            'isManager' => $isManager,
            'isMemberNonExpired' => $isMemberNonExpired,
            'paginator' => $paginator,
        ));
    }

    protected function getPostCourseService()
    {
        return $this->createService('CorporateTrainingBundle:PostCourse:PostCourseService');
    }
}
