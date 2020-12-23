<?php

namespace CorporateTrainingBundle\Biz\OfflineCourse\Service;

interface OfflineCourseService
{
    public function createOfflineCourse($offlineCourse);

    public function updateOfflineCourse($id, $fields);

    public function deleteOfflineCourse($id);

    public function getOfflineCourse($id);

    public function findOfflineCoursesByIds($ids);

    public function searchOfflineCourses($conditions, $orderBys, $start, $limit);

    public function countOfflineCourses($conditions);

    public function publishOfflineCourse($id);

    public function closeOfflineCourse($id);

    public function setTeachers($id, $teacherIds);

    public function findTeachersByOfflineCourseId($id);

    public function findTeachingOfflineCourseByUserId($userId);

    public function findPublishedOfflineCoursesByTeacherIdAndTimeRange($teacherId, $timeRange);

    public function findPublishedCourseByUserIds($userIds);

    public function buildOfflineCourseStatistic($course);
}
