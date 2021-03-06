<?php

namespace AppBundle\Controller\Question;

use Symfony\Component\HttpFoundation\Request;

class UncertainChoiceQuesitonController extends BaseQuestionController
{
    public function showAction(Request $request, $id, $courseId)
    {
        // TODO: Implement showAction() method.
    }

    public function editAction(Request $request, $courseSetId, $questionId)
    {
        list($courseSet, $question) = $this->tryGetCourseSetAndQuestion($courseSetId, $questionId);
        $user = $this->getUser();

        $parentQuestion = array();
        if ($question['parentId'] > 0) {
            $parentQuestion = $this->getQuestionService()->get($question['parentId']);
        }

        $manageCourses = $this->getCourseService()->findUserManageCoursesByCourseSetId($user['id'], $courseSetId);
        $courseTasks = $this->getTaskService()->findTasksByCourseId($courseSet['defaultCourseId']);

        return $this->render('question-manage/uncertain-choice-form.html.twig', array(
            'courseSet' => $courseSet,
            'question' => $question,
            'parentQuestion' => $parentQuestion,
            'type' => $question['type'],
            'courseTasks' => $courseTasks,
            'courses' => $manageCourses,
            'request' => $request,
        ));
    }

    public function createAction(Request $request, $courseSetId, $type)
    {
        $user = $this->getUser();
        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);
        $manageCourses = $this->getCourseService()->findUserManageCoursesByCourseSetId($user['id'], $courseSetId);

        $parentId = $request->query->get('parentId', 0);
        $parentQuestion = $this->getQuestionService()->get($parentId);
        $courseTasks = $this->getTaskService()->findTasksByCourseId($courseSet['defaultCourseId']);

        return $this->render('question-manage/uncertain-choice-form.html.twig', array(
            'courseSet' => $courseSet,
            'parentQuestion' => $parentQuestion,
            'type' => $type,
            'courses' => $manageCourses,
            'courseTasks' => $courseTasks,
        ));
    }
}
