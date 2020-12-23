<?php

namespace CorporateTrainingBundle\Controller\StudyCenter;

use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\Course\CourseBaseController;

class ThreadController extends CourseBaseController
{
    public function indexAction(Request $request, $courseId)
    {
        $filters = $this->getThreadSearchFilters($request);
        $course = $this->getCourseService()->getCourse($courseId);
        $conditions = $this->convertFiltersToConditions($course, $filters);
        $threads = $this->getThreadService()->searchThreads(
            $conditions,
            $filters['sort'],
            0, 5
        );

        $userIds = array_merge(
            ArrayToolkit::column($threads, 'userId'),
            ArrayToolkit::column($threads, 'latestPostUserId')
        );
        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render(
            'study-center/thread/my-task-thread.html.twig',
            array(
                'course' => $course,
                'threads' => $threads,
                'users' => $users,
                'filters' => $filters,
            )
        );
    }

    public function threadsAction(Request $request, $courseId)
    {
        $filters = $this->getThreadSearchFilters($request);
        $course = $this->getCourseService()->getCourse($courseId);
        $conditions = $this->convertFiltersToConditions($course, $filters);
        $threads = $this->getThreadService()->searchThreads(
            $conditions,
            $filters['sort'],
            0, 5
        );

        $userIds = array_merge(
            ArrayToolkit::column($threads, 'userId'),
            ArrayToolkit::column($threads, 'latestPostUserId')
        );
        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render(
            'study-center/thread/list.html.twig',
            array(
                'threads' => $threads,
                'users' => $users,
                'filters' => $filters,
                'course' => $course,
            )
        );
    }

    public function createAction(Request $request, $courseId)
    {
        if ('POST' == $request->getMethod()) {
            $thread = $request->request->all();
            $thread['courseId'] = $courseId;

            if (isset($thread['type']) && 'on' == $thread['type']) {
                $thread['type'] = 'question';
            } else {
                $thread['type'] = 'discussion';
            }

            try {
                $thread = $this->getThreadService()->createThread($thread);
                $attachment = $request->request->get('attachment');
                $this->getUploadFileService()->createUseFiles($attachment['fileIds'], $thread['id'], $attachment['targetType'], $attachment['type']);

                return $this->createJsonResponse(array('url' => $this->generateUrl('study_center_threads', array('courseId' => $courseId, 'type' => 'all', 'sort' => 'posted'))));
            } catch (\Exception $e) {
                return $this->createMessageResponse('error', $e->getMessage(), '错误提示', 1, $request->getPathInfo());
            }
        }

        return $this->redirect($this->generateUrl('homepage'));
    }

    protected function convertFiltersToConditions($course, $filters)
    {
        $conditions = array('courseId' => $course['id']);

        if (!empty($filters['userId'])) {
            $conditions['userId'] = $filters['userId'];
        }

        switch ($filters['type']) {
            case 'question':
                $conditions['type'] = 'question';
                break;
            case 'elite':
                $conditions['isElite'] = 1;
                break;
            default:
                break;
        }

        return $conditions;
    }

    protected function getThreadSearchFilters($request)
    {
        $filters = array();
        $filters['type'] = $request->query->get('type');

        if (!in_array($filters['type'], array('all', 'question', 'elite'))) {
            $filters['type'] = 'all';
        }

        $filters['sort'] = $request->query->get('sort');

        if (!in_array($filters['sort'], array('created', 'posted', 'createdNotStick', 'postedNotStick'))) {
            $filters['sort'] = 'posted';
        }

        $filters['userId'] = $request->query->get('userId');

        return $filters;
    }

    protected function getThreadService()
    {
        return $this->createService('Course:ThreadService');
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getNotifiactionService()
    {
        return $this->createService('User:NotificationService');
    }

    protected function getOrgService()
    {
        return $this->createService('Org:OrgService');
    }

    protected function getPostService()
    {
        return $this->createService('CorporateTrainingBundle:Post:PostService');
    }

    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }
}
