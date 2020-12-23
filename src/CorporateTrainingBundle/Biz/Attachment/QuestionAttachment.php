<?php

namespace CorporateTrainingBundle\Biz\Attachment;

use AppBundle\Common\ArrayToolkit;
use ExamPlugin\Biz\TestPaper\Service\TestPaperService;

class QuestionAttachment extends BaseAttachment
{
    public function canDelete($fileId)
    {
        $user = $this->getCurrentUser();
        $fileUsed = $this->getUploadFileService()->getUseFile($fileId);

        if (empty($fileUsed)) {
            return true;
        }

        $question = $this->getQuestionService()->get($fileUsed['targetId']);

        if ($this->targetType != $fileUsed['targetType']) {
            return false;
        }

        if ($user['id'] == $question['createdUserId']) {
            return true;
        }

        if ('exam' == $question['target']) {
            if ($this->getExamQuestionService()->canManageQuestion()) {
                return true;
            }
        }

        if ('exam' != $question['target']) {
            $course = $this->getCourseService()->getDefaultCourseByCourseSetId($question['courseSetId']);
            if ($this->getCourseService()->canManageCourse($course['id'], $question['courseSetId'])) {
                return true;
            }
        }

        return false;
    }

    public function canOperate($fileId, $type = '')
    {
        $user = $this->getCurrentUser();
        $fileUsed = $this->getUploadFileService()->getUseFile($fileId);
        $question = $this->getQuestionService()->get($fileUsed['targetId']);

        if ($this->targetType != $fileUsed['targetType']) {
            return false;
        }

        if ($user['id'] == $question['createdUserId']) {
            return true;
        }

        if ('exam' == $question['target']) {
            $examMembers = $this->getExamService()->findMembersByUserId($user['id']);
            $examIds = ArrayToolkit::column($examMembers, 'examId');
            $exams = $this->getExamService()->searchExams(array('ids' => $examIds, 'endTime_GE' => time()), array(), 0, PHP_INT_MAX);

            if (empty($exams)) {
                return false;
            }

            $testPaperIds = ArrayToolkit::column($exams, 'testPaperId');

            $childTestPapers = $this->getTestPaperService()->findTestPapersByParentIds($testPaperIds);

            $childTestPaperIds = ArrayToolkit::column($childTestPapers, 'id');

            $testPaperIds = array_merge($testPaperIds, $childTestPaperIds);

            $testPaperItems = $this->getTestPaperService()->findItemsByTestPaperIds($testPaperIds);
            $questionIds = ArrayToolkit::column($testPaperItems, 'questionId');

            if (in_array($question['id'], $questionIds)) {
                return true;
            }

            if ($this->getExamQuestionService()->canManageQuestion()) {
                return true;
            }
        }

        if ('exam' != $question['target']) {
            $course = $this->getCourseService()->getDefaultCourseByCourseSetId($question['courseSetId']);
            if ($this->getCourseService()->canManageCourse($course['id'], $question['courseSetId'])) {
                return true;
            }

            if ($this->getCourseMemberService()->isCourseMember($course['id'], $user['id'])) {
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
     * @return TestPaperService
     */
    protected function getTestPaperService()
    {
        return $this->createService('ExamPlugin:TestPaper:TestPaperService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\Course\Service\Impl\CourseServiceImpl
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return \Biz\Course\Service\Impl\MemberServiceImpl
     */
    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }
}
