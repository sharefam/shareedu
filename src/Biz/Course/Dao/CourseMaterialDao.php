<?php

namespace Biz\Course\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface CourseMaterialDao extends GeneralDaoInterface
{
    public function findByCopyIdAndLockedCourseIds($copyId, $courseIds);

    public function findMaterialsByLessonIdAndSource($lessonId, $source);

    public function findByLessonIdAndType($lessonId, $type);

    public function countGroupByFileIds(array $conditions);

    public function deleteByLessonId($lessonId, $courseType);

    public function findByCourseIdAndCourseType($courseId, $courseType);

    public function deleteByCourseId($courseId, $courseType);

    public function findByCourseSetIdAndCourseType($courseSetId, $courseType);

    public function deleteByCourseSetId($courseSetId, $courseType);

    public function deleteByFileId($fileId);

    public function searchDistinctFileIds($conditions, $orderBys, $start, $limit);

    public function countGroupByFileId($conditions);
}
