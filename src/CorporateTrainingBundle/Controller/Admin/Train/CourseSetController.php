<?php

namespace CorporateTrainingBundle\Controller\Admin\Train;

use AppBundle\Common\Paginator;
use AppBundle\Controller\Admin\BaseController;
use Biz\Course\Service\ThreadService;
use CorporateTrainingBundle\Biz\Classroom\Service\ClassroomService;
use CorporateTrainingBundle\Biz\Course\Service\CourseService;
use CorporateTrainingBundle\Biz\Course\Service\CourseSetService;
use CorporateTrainingBundle\Biz\Testpaper\Service\TestpaperService;
use SurveyPlugin\Biz\Survey\Service\SurveyService;
use Symfony\Component\HttpFoundation\Request;

class CourseSetController extends BaseController
{
    public function teachingAction(Request $request, $filter = 'normal')
    {
        $user = $this->getCurrentUser();

        if (!$user->isTeacher()) {
            return $this->createMessageResponse('error', $this->trans('my.classroom.teaching.no_permission'));
        }

        $conditions = array(
            'type' => $filter,
        );

        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseSetService()->countUserTeachingCourseSets($user['id'], $conditions),
            20
        );

        $courseSets = $this->getCourseSetService()->searchUserTeachingCourseSets(
            $user['id'],
            $conditions,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        foreach ($courseSets as &$courseSet) {
            $courseSet['questionNum'] = $this->getThreadService()->countThreads(array('courseSetId' => $courseSet['id'], 'type' => 'question'));
            $courseSet['discussionNum'] = $this->getThreadService()->countThreads(array('courseSetId' => $courseSet['id'], 'type' => 'discussion'));
            $courseSet['testpaperNum'] = $this->getTestpaperService()->SumPaperResultsStatusNumByCourseIdAndType($courseSet['defaultCourseId'], 'testpaper');
            $courseSet['homeworkNum'] = $this->getTestpaperService()->SumPaperResultsStatusNumByCourseIdAndType($courseSet['defaultCourseId'], 'homework');
        }

        $service = $this->getCourseService();
        $courseSets = array_map(
            function ($set) use ($service) {
                $set['courseNum'] = $service->countCourses(array(
                    'courseSetId' => $set['id'],
                ));

                return $set;
            },
            $courseSets
        );

        if ($this->isPluginInstalled('Survey')) {
            foreach ($courseSets as &$courseSet) {
                $courseSet['surveyScore'] = $this->getSurveyResultService()->getOnlineCourseSurveyScoreByCourseId($courseSet['defaultCourseId']);
            }
        }

        return $this->render(
            'CorporateTrainingBundle::admin/train/teach-manage/course-sets.html.twig',
            array(
                'courseSets' => $courseSets,
                'paginator' => $paginator,
                'filter' => $filter,
            )
        );
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('CorporateTrainingBundle:Course:CourseSetService');
    }

    /**
     * @return SurveyService
     */
    protected function getSurveyResultService()
    {
        return $this->createService('SurveyPlugin:Survey:SurveyResultService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return ThreadService
     */
    protected function getThreadService()
    {
        return $this->createService('Course:ThreadService');
    }

    /**
     * @return TestpaperService
     */
    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }
}
