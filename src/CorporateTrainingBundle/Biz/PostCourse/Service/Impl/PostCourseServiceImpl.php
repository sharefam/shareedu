<?php

namespace CorporateTrainingBundle\Biz\PostCourse\Service\Impl;

use Biz\BaseService;
use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\CourseSetService;
use CorporateTrainingBundle\Biz\PostCourse\Service\PostCourseService;
use CustomBundle\Biz\Course\Service\CourseService;

class PostCourseServiceImpl extends BaseService implements PostCourseService
{
    public function createPostCourse($postCourse)
    {
        $this->validateFields($postCourse);
        $postCourse = $this->filterFields($postCourse);

        $this->checkPostExist($postCourse['postId']);
        $this->checkCourseSetExist($postCourse['courseSetId']);
        $this->checkCourseExist($postCourse['courseId']);
        $this->checkCourseSetExistInPost($postCourse['postId'], $postCourse['courseSetId']);
        $this->checkCourseExistInPost($postCourse['postId'], $postCourse['courseId']);

        return $this->getPostCourseDao()->create($postCourse);
    }

    public function batchCreatePostCourses($postId, $courseIds)
    {
        try {
            $this->biz['db']->beginTransaction();

            $count = $this->countPostCourses(array('postId' => $postId));
            $courses = $this->getCourseService()->findCoursesByIds($courseIds);
            foreach ($courses as $course) {
                $postCourse = array(
                    'postId' => $postId,
                    'courseId' => $course['id'],
                    'courseSetId' => $course['courseSetId'],
                    'seq' => ++$count,
                );
                $this->createPostCourse($postCourse);
            }

            $this->dispatchEvent('batch.create.post_courses', array('postId' => $postId, 'courses' => $courses));
            $this->biz['db']->commit();
        } catch (\Exception $e) {
            $this->biz['db']->rollback();
            throw $e;
        }
    }

    public function updatePostCourse($id, $fields)
    {
        $fields = $this->filterFields($fields);

        $this->checkPostCourseExist($id);

        if (isset($fields['courseSetId'])) {
            $this->checkCourseSetExist($fields['courseSetId']);
        }

        if (isset($fields['courseId'])) {
            $this->checkCourseExist($fields['courseId']);
        }

        return $this->getPostCourseDao()->update($id, $fields);
    }

    public function deletePostCourse($id)
    {
        $this->checkPostCourseExist($id);

        return $this->getPostCourseDao()->delete($id);
    }

    public function deletePostCourseByPostId($postId)
    {
        $this->getPostCourseDao()->deleteByPostId($postId);
    }

    public function batchDeletePostCourses($ids)
    {
        foreach ($ids as $id) {
            $this->deletePostCourse($id);
        }
    }

    public function getPostCourse($id)
    {
        return $this->getPostCourseDao()->get($id);
    }

    public function getPostCourseByPostIdAndCourseId($postId, $courseId)
    {
        return $this->getPostCourseDao()->getByPostIdAndCourseId($postId, $courseId);
    }

    public function getPostCourseByPostIdAndCourseSetId($postId, $courseSetId)
    {
        return $this->getPostCourseDao()->getByPostIdAndCourseSetId($postId, $courseSetId);
    }

    public function findPostCoursesByPostId($postId)
    {
        return $this->getPostCourseDao()->findByPostId($postId);
    }

    public function findPostCoursesByCourseId($courseId)
    {
        return $this->getPostCourseDao()->findByCourseId($courseId);
    }

    //岗位课程可能并没有开始学习，无法通过course_member来查询
    public function findUserLearningPostCourses($userId, $courseIds)
    {
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);
        $learnedPostCourses = $this->findUserLearnedPostCourses($userId, $courseIds);

        return $this->filterLearnedCourse(ArrayToolkit::column($learnedPostCourses, 'id'), $courses);
    }

    public function countUserLearnedPostCourses($userId, $courseIds)
    {
        $conditions = array(
            'm.userId' => $userId,
            'm.courseId' => $courseIds,
        );

        return $this->getMemberDao()->countLearnedMembers($conditions);
    }

    public function findUserLearnedPostCourses($userId, $courseIds)
    {
        $conditions = array(
            'm.userId' => $userId,
            'm.courseId' => $courseIds,
        );

        $members = $this->getMemberDao()->findLearnedMembers($conditions, 0, PHP_INT_MAX);

        return $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($members, 'courseId'));
    }

    public function searchPostCourses($conditions, $orderBy, $start, $limit)
    {
        return $this->getPostCourseDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function countPostCourses($conditions)
    {
        return $this->getPostCourseDao()->count($conditions);
    }

    public function isCourseExistInPost($courseId, $postId)
    {
        $result = false;
        $postCourse = $this->getPostCourseByPostIdAndCourseId($postId, $courseId);
        if (!empty($postCourse)) {
            $result = true;
        }

        return $result;
    }

    public function sortPostCourses($ids)
    {
        foreach ($ids as $index => $id) {
            $this->getPostCourseDao()->update($id, array('seq' => $index + 1));
        }
    }

    public function deletePostCoursesByCourseSetId($courseSetId)
    {
        $this->getPostCourseDao()->deleteByCourseSetId($courseSetId);
    }

    public function countFinishedPostCoursesByPostIdAndUserId($postId, $userId)
    {
        $postCourses = $this->findPostCoursesByPostId($postId);
        if (empty($postCourses)) {
            return 0;
        }
        $courseIds = ArrayToolkit::column($postCourses, 'courseId');

        return $this->countUserLearnedPostCourses($userId, $courseIds);
    }

    private function checkPostExist($postId)
    {
        $post = $this->getPostService()->getPost($postId);

        if (empty($post)) {
            throw $this->createNotFoundException("Post {$postId} does not exist!");
        }
    }

    private function checkCourseExist($courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        if (empty($course)) {
            throw $this->createNotFoundException("course {$courseId} does not exist!");
        }
    }

    private function checkCourseSetExist($courseSetId)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);

        if (empty($courseSet)) {
            throw $this->createNotFoundException("CourseSet{$courseSetId} does not exist!");
        }
    }

    private function checkPostCourseExist($postCourseId)
    {
        $postCourse = $this->getPostCourse($postCourseId);

        if (empty($postCourse)) {
            throw $this->createNotFoundException("Post course {$postCourseId} does not exist!");
        }
    }

    private function checkCourseSetExistInPost($postId, $courseSetId)
    {
        $postCourse = $this->getPostCourseByPostIdAndCourseSetId($postId, $courseSetId);

        if (!empty($postCourse)) {
            throw $this->createInvalidArgumentException("Course {$courseSetId} already exists in post {$postId}");
        }
    }

    private function checkCourseExistInPost($postId, $courseId)
    {
        $postCourse = $this->getPostCourseByPostIdAndCourseId($postId, $courseId);

        if (!empty($postCourse)) {
            throw $this->createInvalidArgumentException("Course {$courseId} already exists in the post {$postId}");
        }
    }

    private function validateFields($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('postId', 'courseSetId', 'courseId', 'seq'))) {
            throw $this->createInvalidArgumentException('Lack of required fields');
        }
    }

    private function filterFields($fields)
    {
        return ArrayToolkit::parts($fields, array('postId', 'courseSetId', 'courseId', 'seq'));
    }

    private function filterLearnedCourse($learnedCourseIds, $courses)
    {
        foreach ($learnedCourseIds as $learnedCourseId) {
            if (in_array($learnedCourseId, ArrayToolkit::column($courses, 'id'))) {
                unset($courses[$learnedCourseId]);
            }
        }

        return $courses;
    }

    protected function getPostService()
    {
        return $this->createService('CorporateTrainingBundle:Post:PostService');
    }

    protected function getPostCourseDao()
    {
        return $this->createDao('CorporateTrainingBundle:PostCourse:PostCourseDao');
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
        return $this->createService('Course:CourseSetService');
    }

    protected function getMemberDao()
    {
        return $this->createDao('Course:CourseMemberDao');
    }
}
