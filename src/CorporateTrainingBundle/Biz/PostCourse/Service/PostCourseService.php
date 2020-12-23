<?php

namespace CorporateTrainingBundle\Biz\PostCourse\Service;

interface PostCourseService
{
    public function createPostCourse($postCourse);

    public function batchCreatePostCourses($postId, $courseIds);

    public function updatePostCourse($id, $fields);

    public function deletePostCourse($id);

    public function deletePostCourseByPostId($postId);

    public function batchDeletePostCourses($ids);

    public function getPostCourse($id);

    public function getPostCourseByPostIdAndCourseId($postId, $courseId);

    public function getPostCourseByPostIdAndCourseSetId($postId, $courseId);

    public function findPostCoursesByPostId($postId);

    public function findPostCoursesByCourseId($courseId);

    public function findUserLearningPostCourses($userId, $courseIds);

    public function findUserLearnedPostCourses($userId, $courseIds);

    public function searchPostCourses($conditions, $orderBy, $start, $limit);

    public function countPostCourses($conditions);

    public function isCourseExistInPost($courseId, $postId);

    public function sortPostCourses($ids);

    public function deletePostCoursesByCourseSetId($courseSetId);

    public function countFinishedPostCoursesByPostIdAndUserId($postId, $userId);
}
