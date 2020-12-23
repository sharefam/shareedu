<?php

namespace CorporateTrainingBundle\Controller\OfflineCourse\Task;

use Symfony\Component\HttpFoundation\Request;

class OfflineCourseTaskController extends BaseTaskController
{
    public function createAction(Request $request, $id, $type)
    {
        $offlineCourse = $this->getOfflineCourseService()->getOfflineCourse($id);

        return $this->render(
            'CorporateTrainingBundle::offline-course-manage/task/create-modal.html.twig',
            array(
                'course' => $offlineCourse,
                'type' => $type,
            )
        );
    }

    public function editAction(Request $request, $task, $activity)
    {
        return $this->render(
            'CorporateTrainingBundle::offline-course-manage/task/edit-modal.html.twig',
            array(
                'task' => $task,
                'activity' => $activity,
            )
        );
    }
}
