<?php

namespace CorporateTrainingBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\LearningDataAnalysisService;
use CorporateTrainingBundle\Biz\PostCourse\Service\PostCourseService;

class MePostCoursesProgress extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $progress = array(
            'totalCount' => 0,
            'finishedCount' => 0,
        );

        $user = $this->getCurrentUser();
        if (empty($user) || empty($user['postId'])) {
            return $progress;
        }

        $postCourses = $this->getPostCourseService()->findPostCoursesByPostId($user['postId']);
        if (empty($postCourses)) {
            return $progress;
        }

        $progress['totalCount'] = count($postCourses);
        $courseIds = ArrayToolkit::column($postCourses, 'courseId');
        $progress['finishedCount'] = $this->getUserPostCoursesLearnedCount($user['id'], $courseIds);

        return $progress;
    }

    protected function getUserPostCoursesLearnedCount($userId, $courseIds)
    {
        $learnedCount = 0;
        foreach ($courseIds as $courseId) {
            $progress = $this->getCourseLearningDataAnalysisService()->getUserLearningProgress($courseId, $userId);
            if ($progress['finishedCount'] >= $progress['total'] && $progress['finishedCount'] != 0) {
                ++$learnedCount;
            }
        }

        return $learnedCount;
    }

    /**
     * @return PostCourseService
     */
    protected function getPostCourseService()
    {
        return $this->service('CorporateTrainingBundle:PostCourse:PostCourseService');
    }

    /**
     * @return LearningDataAnalysisService
     */
    protected function getCourseLearningDataAnalysisService()
    {
        return $this->service('Course:LearningDataAnalysisService');
    }
}
