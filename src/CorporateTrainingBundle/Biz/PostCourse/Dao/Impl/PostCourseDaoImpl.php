<?php

namespace CorporateTrainingBundle\Biz\PostCourse\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use CorporateTrainingBundle\Biz\PostCourse\Dao\PostCourseDao;

class PostCourseDaoImpl extends GeneralDaoImpl implements PostCourseDao
{
    protected $table = 'post_course';

    public function deleteByPostId($postId)
    {
        $sql = "DELETE FROM {$this->table} WHERE postId = ?";

        return $this->db()->executeUpdate($sql, array($postId));
    }

    public function deleteByCourseSetId($courseSetId)
    {
        $sql = "DELETE FROM {$this->table} WHERE courseSetId = ?";

        return $this->db()->executeUpdate($sql, array($courseSetId));
    }

    public function getByPostIdAndCourseId($postId, $courseId)
    {
        return $this->getByFields(array('postId' => $postId, 'courseId' => $courseId));
    }

    public function getByPostIdAndCourseSetId($postId, $courseSetId)
    {
        return $this->getByFields(array('postId' => $postId, 'courseSetId' => $courseSetId));
    }

    public function findByPostId($postId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE postId = ?";

        return $this->db()->fetchAll($sql, array($postId));
    }

    public function findByCourseId($courseId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE courseId = ?";

        return $this->db()->fetchAll($sql, array($courseId));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'serializes' => array(),
            'orderbys' => array('id', 'seq'),
            'conditions' => array(
                'courseId = :courseId',
                'postId = :postId',
            ),
        );
    }
}
