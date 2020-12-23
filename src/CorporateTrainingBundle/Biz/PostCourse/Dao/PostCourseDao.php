<?php

namespace CorporateTrainingBundle\Biz\PostCourse\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface PostCourseDao extends GeneralDaoInterface
{
    public function deleteByPostId($postId);

    public function deleteByCourseSetId($courseSetId);

    public function getByPostIdAndCourseId($postId, $courseId);

    public function getByPostIdAndCourseSetId($postId, $courseSetId);

    public function findByPostId($postId);

    public function findByCourseId($courseId);
}
