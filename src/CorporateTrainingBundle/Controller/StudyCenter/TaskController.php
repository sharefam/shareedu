<?php

namespace CorporateTrainingBundle\Controller\StudyCenter;

use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\Course\CourseController as BaseCourseController;

class TaskController extends BaseCourseController
{
    public function listAction(Request $request, $course, $tab_type, $userId)
    {
        $courseItems = $this->getCourseService()->findCourseItemsByUserId($course['id'], $userId);

        $user = $this->getUserService()->getUser($userId);
        if (empty($user)) {
            $user = $this->getCurrentUser();
        }

        $member = $this->getMemberService()->findMemberUserIdsByCourseId($course['id']);
        if (!in_array($user['id'], $member)) {
            $this->getMemberService()->becomeStudent($course['id'], $user['id']);
        }

        $files = $this->extractTaskFromCourseItems($course, $courseItems);

        list($isMarketingPage, $member) = $this->isMarketingPage($course['id'], $member);

        return $this->render(
            'study-center/widget/list.html.twig',
            array(
                'course' => $course,
                'courseItems' => $courseItems,
                'member' => $member,
                'files' => $files,
                'isMarketingPage' => $isMarketingPage,
                'tab_type' => $tab_type,
                'userId' => $userId,
            )
        );
    }

    protected function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }

    protected function extractTaskFromCourseItems($course, $courseItems)
    {
        $tasks = array();
        if ($course['courseType'] == 'normal') {
            array_walk(
                $courseItems,
                function ($item) use (&$tasks) {
                    if (isset($item['activity'])) {
                        $tasks[] = $item;
                    }
                }
            );
        } else {
            array_walk(
                $courseItems,
                function ($item) use (&$tasks) {
                    if ($item['type'] === 'lesson') {
                        $tasks = array_merge($tasks, $item['tasks']);
                    }
                }
            );
        }

        return $tasks;
    }
}
