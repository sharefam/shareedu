<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\Exception\AccessDeniedException;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\ReportService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class CourseReport extends AbstractResource
{
    public function get(ApiRequest $request, $courseId, $reportType)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        if (!$course) {
            throw new NotFoundHttpException('教学计划不存在', null, ErrorCode::RESOURCE_NOT_FOUND);
        }

        $currentUser = $this->getCurrentUser();
        if (!$currentUser->hasPermission('admin_train_teach_manage_my_teaching_courses_manage') && !$currentUser->hasPermission('admin_course_manage')) {
            throw new AccessDeniedException();
        }

        switch ($reportType) {
            case 'completion_rate_trend':
                $result = $this->getCompletionRateTrend($request, $courseId);
                break;
            case 'student_trend':
                $result = $this->getStudentTrend($request, $courseId);
                break;
            case 'student_detail':
                $result = $this->getStudentDetail($request, $courseId);
                break;
            default:
                throw new UnprocessableEntityHttpException();
        }

        return $result;
    }

    private function getCompletionRateTrend(ApiRequest $request, $courseId)
    {
        $startDate = $request->query->get('startDate', date('Y-m-d', strtotime('-6 days')));
        $endDate = $request->query->get('endDate', date('Y-m-d'));

        return $this->getReportService()->getCompletionRateTrend($courseId, $startDate, $endDate);
    }

    private function getStudentTrend(ApiRequest $request, $courseId)
    {
        $startDate = $request->query->get('startDate', date('Y-m-d', strtotime('-6 days')));
        $endDate = $request->query->get('endDate', date('Y-m-d'));

        return $this->getReportService()->getStudentTrend($courseId, array('startDate' => $startDate, 'endDate' => $endDate));
    }

    private function getStudentDetail(ApiRequest $request, $courseId)
    {
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $sort = $request->query->get('sort', 'createdTimeDesc');
        $filter = $request->query->get('filter', 'all');
        $userIds = $this->getReportService()->searchUserIdsByCourseIdAndFilterAndSortAndKeyword($courseId, $filter, $sort, $offset, $limit);

        return $this->getReportService()->getStudentDetail($courseId, $userIds);
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return ReportService
     */
    private function getReportService()
    {
        return $this->service('Course:ReportService');
    }
}
