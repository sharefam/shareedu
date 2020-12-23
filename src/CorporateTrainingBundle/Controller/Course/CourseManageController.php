<?php

namespace CorporateTrainingBundle\Controller\Course;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Controller\Course\CourseManageController as BaseController;
use Symfony\Component\HttpFoundation\Request;

class CourseManageController extends BaseController
{
    public function marketingAction(Request $request, $courseSetId, $courseId)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($courseSetId);
        $freeTasks = $this->getTaskService()->findFreeTasksByCourseId($courseId);
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            if (empty($data['enableBuyExpiryTime'])) {
                unset($data['buyExpiryTime']);
            }

            $data = $this->prepareExpiryMode($data);

            if (!empty($data['services'])) {
                $data['services'] = json_decode($data['services'], true);
            }

            $freeTaskIds = ArrayToolkit::column($freeTasks, 'id');
            $this->getTaskService()->updateTasks($freeTaskIds, array('isFree' => 0));
            if (!empty($data['freeTaskIds'])) {
                $canFreeTaskIds = $data['freeTaskIds'];
                $this->getTaskService()->updateTasks($canFreeTaskIds, array('isFree' => 1));
                unset($data['freeTaskIds']);
            }

            $this->getCourseService()->updateCourseMarketing($courseId, $data);
            $this->setFlashMessage('success', 'course.course_manage.marketing.message.update_success');

            return $this->redirect(
                $this->generateUrl(
                    'course_set_manage_course_marketing',
                    array('courseSetId' => $courseSetId, 'courseId' => $courseId)
                )
            );
        }

        if ($courseSet['locked']) {
            return $this->redirectToRoute(
                'course_set_manage_sync',
                array(
                    'id' => $courseSetId,
                    'sideNav' => 'marketing',
                )
            );
        }

        $course = $this->getCourseService()->getCourse($courseId);

        //prepare form data
        if ('end_date' == $course['expiryMode']) {
            $course['deadlineType'] = 'end_date';
            $course['expiryMode'] = 'days';
        }

        return $this->render(
            'course-manage/marketing.html.twig',
            array(
                'courseSet' => $courseSet,
                'course' => $this->formatCourseDate($course),
                'canFreeTasks' => $this->findCanFreeTasks($course),
                'freeTasks' => $freeTasks,
            )
        );
    }

    private function findCanFreeTasks($course)
    {
        $conditions = array(
            'courseId' => $course['id'],
            'types' => array('text', 'video', 'audio', 'flash', 'doc', 'ppt'),
            'isOptional' => 0,
        );

        return $this->getTaskService()->searchTasks($conditions, array('seq' => 'ASC'), 0, PHP_INT_MAX);
    }

    public function overviewAction(Request $request, $courseSetId, $courseId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);

        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);

        $summary = $this->getReportService()->summary($course['id']);
        $totalLearnTime = $this->getTaskResultService()->sumLearnTimeByCourseId($courseId);

        return $this->render(
            'course-manage/overview/overview.html.twig',
            array(
                'summary' => $summary,
                'courseSet' => $courseSet,
                'course' => $course,
                'totalLearnTime' => $totalLearnTime,
            )
        );
    }
}
