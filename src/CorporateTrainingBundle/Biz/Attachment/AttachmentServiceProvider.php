<?php

namespace CorporateTrainingBundle\Biz\Attachment;

use AppBundle\Extension\Extension;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class AttachmentServiceProvider extends Extension implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        $this->registerAttachmentType($biz);
        $biz['attachment_factory'] = function ($biz) {
            $attachment = new AttachmentFactory();
            $attachment->setBiz($biz);

            return $attachment;
        };
    }

    protected function registerAttachmentType($biz)
    {
        $biz['attachment.permission.project.plan.enroll'] = function ($biz) {
            return new ProjectPlanEnrollAttachment($biz);
        };

        $biz['attachment.permission.projectPlaning.offline.homework'] = function ($biz) {
            return new OfflineCourseHomeworkAttachment($biz);
        };

        $biz['attachment.permission.projectPlaning.material1'] = function ($biz) {
            return new ProjectPlanEnrollAttachment($biz);
        };

        $biz['attachment.permission.projectPlaning.material2'] = function ($biz) {
            return new ProjectPlanEnrollAttachment($biz);
        };

        $biz['attachment.permission.projectPlaning.material3'] = function ($biz) {
            return new ProjectPlanEnrollAttachment($biz);
        };

        $biz['attachment.permission.course.thread'] = function ($biz) {
            return new CourseThreadAttachment($biz);
        };

        $biz['attachment.permission.course.thread.post'] = function ($biz) {
            return new CourseThreadPostAttachment($biz);
        };

        $biz['attachment.permission.classroom.thread'] = function ($biz) {
            return new ClassroomThreadAttachment($biz);
        };

        $biz['attachment.permission.classroom.thread.post'] = function ($biz) {
            return new ClassroomThreadPostAttachment($biz);
        };

        $biz['attachment.permission.question.stem'] = function ($biz) {
            return new QuestionStemAttachment($biz);
        };

        $biz['attachment.permission.exam.question.answer'] = function ($biz) {
            return new QuestionAnswerAttachment($biz);
        };

        $biz['attachment.permission.question.answer'] = function ($biz) {
            return new QuestionAnswerAttachment($biz);
        };

        $biz['attachment.permission.question.analysis'] = function ($biz) {
            return new QuestionAnalysisAttachment($biz);
        };

        $biz['attachment.permission.qa.thread'] = function ($biz) {
            return new QAThreadAttachment($biz);
        };

        $biz['attachment.permission.qa.thread.post'] = function ($biz) {
            return new QAThreadPostAttachment($biz);
        };

        $biz['attachment.permission.group.thread'] = function ($biz) {
            return new GroupThreadAttachment($biz);
        };

        $biz['attachment.permission.group.thread.post'] = function ($biz) {
            return new GroupThreadPostAttachment($biz);
        };

        $biz['attachment.permission.article'] = function ($biz) {
            return new ArticleAttachment($biz);
        };
    }
}
