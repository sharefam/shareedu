<?php

namespace CorporateTrainingBundle\Biz\Attachment;

class CourseThreadAttachment extends BaseAttachment
{
    public function canOperate($fileId, $type = '')
    {
        $user = $this->getCurrentUser();
        $fileUsed = $this->getUploadFileService()->getUseFile($fileId);
        if (empty($fileUsed) && 'delete' == $type) {
            return true;
        }
        $thread = $this->getCourseThreadService()->getThread(null, $fileUsed['targetId']);
        if ('course.thread' != $fileUsed['targetType']) {
            return false;
        }

        if ($thread['userId'] == $user['id']) {
            return true;
        }

        if ($this->getCourseService()->canManageCourse($thread['courseId'])) {
            return true;
        }

        if ($this->getCourseMemberService()->isCourseMember($thread['courseId'], $user['id'])) {
            return true;
        }

        return false;
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

    /**
     * @return \Biz\Course\Service\Impl\ThreadServiceImpl
     */
    protected function getCourseThreadService()
    {
        return $this->createService('Course:ThreadService');
    }
}
