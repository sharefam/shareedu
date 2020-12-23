<?php

namespace CorporateTrainingBundle\Biz\Attachment;

class QuestionAnswerAttachment extends BaseAttachment
{
    public function canOperate($fileId, $type = '')
    {
        $user = $this->getCurrentUser();
        $fileUsed = $this->getUploadFileService()->getUseFile($fileId);
        if (empty($fileUsed) && 'delete' == $type) {
            return true;
        }
        if ('exam.question.answer' == $fileUsed['targetType']) {
            $testPaperItemResult = $this->getExamTestPaperService()->getTestPaperItemResult($fileUsed['targetId']);
        } else {
            $testPaperItemResult = $this->getTestPaperService()->getItemResult($fileUsed['targetId']);
        }

        $question = $this->getQuestionService()->get($testPaperItemResult['questionId']);
        if ($user['id'] == $question['createdUserId']) {
            return true;
        }

        if ($user['id'] == $testPaperItemResult['userId']) {
            return true;
        }

        if ('exam' == $question['target']) {
            if ($this->getExamQuestionService()->canManageQuestion()) {
                return true;
            }
        } else {
            $course = $this->getCourseService()->getDefaultCourseByCourseSetId($question['courseSetId']);
            if ($this->getCourseService()->canManageCourse($course['id'], $question['courseSetId'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineCourse\Service\Impl\OfflineCourseServiceImpl
     */
    protected function getOfflineCourseService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineCourse:OfflineCourseService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineCourse\Service\Impl\TaskServiceImpl
     */
    protected function getOfflineCourseTaskService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineCourse:TaskService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\Impl\ProjectPlanServiceImpl
     */
    protected function getProjectPlanService()
    {
        return $this->createService('ProjectPlan:ProjectPlanService');
    }

    /**
     * @return \Biz\Question\Service\Impl\QuestionServiceImpl
     */
    protected function getQuestionService()
    {
        return $this->createService('Question:QuestionService');
    }

    /**
     * @return \ExamPlugin\Biz\Question\Service\Impl\QuestionServiceImpl
     */
    protected function getExamQuestionService()
    {
        return $this->createService('ExamPlugin:Question:QuestionService');
    }

    /**
     * @return \ExamPlugin\Biz\Exam\Service\Impl\ExamServiceImpl
     */
    protected function getExamService()
    {
        return $this->createService('ExamPlugin:Exam:ExamService');
    }

    /**
     * @return \ExamPlugin\Biz\TestPaper\Service\Impl\TestPaperServiceImpl
     */
    protected function getExamTestPaperService()
    {
        return $this->createService('ExamPlugin:TestPaper:TestPaperService');
    }

    /**
     * @return \Biz\Testpaper\Service\Impl\TestpaperServiceImpl
     */
    protected function getTestPaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\Course\Service\Impl\CourseServiceImpl
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }
}
