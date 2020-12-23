<?php

namespace CorporateTrainingBundle\Controller\Admin\Train;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\Admin\BaseController;
use CorporateTrainingBundle\Biz\Classroom\Service\ClassroomService;
use CorporateTrainingBundle\Biz\Testpaper\Service\TestpaperService;
use Symfony\Component\HttpFoundation\Request;

class ClassroomController extends BaseController
{
    public function teachingAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if (!$user->isTeacher()) {
            return $this->createMessageResponse('error', 'my.classroom.teaching.no_permission');
        }

        $classrooms = $this->getClassroomService()->searchMembers(array('role' => 'teacher', 'userId' => $user->getId()), array('createdTime' => 'desc'), 0, PHP_INT_MAX);
        $classrooms = array_merge($classrooms, $this->getClassroomService()->searchMembers(array('role' => 'assistant', 'userId' => $user->getId()), array('createdTime' => 'desc'), 0, PHP_INT_MAX));
        $classroomIds = ArrayToolkit::column($classrooms, 'classroomId');

        $paginator = new Paginator(
            $this->get('request'),
            count($classroomIds),
            20
        );

        $classroomIds = empty($classroomIds) ? array(-1) : $classroomIds;
        $classrooms = $this->getClassroomService()->searchClassrooms(
            array('classroomIds' => $classroomIds),
            array(),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        foreach ($classrooms as &$classroom) {
            $courses = $this->getClassroomService()->findActiveCoursesByClassroomId($classroom['id']);
            $courseIds = ArrayToolkit::column($courses, 'id');
            $coursesCount = count($courses);

            $classroom['coursesCount'] = $coursesCount;

            $studentCount = $this->getClassroomService()->searchMemberCount(array('role' => 'student', 'classroomId' => $classroom['id']));

            $classroom['studentCount'] = $studentCount;

            $classroom['testpaperNum'] = $this->getTestpaperService()->SumPaperResultsStatusNumByCourseIdsAndType($courseIds, 'testpaper');

            $classroom['homeworkNum'] = $this->getTestpaperService()->SumPaperResultsStatusNumByCourseIdsAndType($courseIds, 'homework');
        }

        return $this->render('CorporateTrainingBundle::admin/train/teach-manage/classrooms.html.twig',
            array(
                'classrooms' => $classrooms,
                'paginator' => $paginator,
            )
        );
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return TestpaperService
     */
    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }
}
