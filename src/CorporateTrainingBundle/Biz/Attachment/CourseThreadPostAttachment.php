<?php

namespace CorporateTrainingBundle\Biz\Attachment;

class CourseThreadPostAttachment extends BaseAttachment
{
    public function canOperate($fileId, $type = '')
    {
        $user = $this->getCurrentUser();
        $fileUsed = $this->getUploadFileService()->getUseFile($fileId);
        if (empty($fileUsed) && 'delete' == $type) {
            return true;
        }
        $post = $this->getCourseThreadService()->getPost(null, $fileUsed['targetId']);

        if ('course.thread.post' != $fileUsed['targetType']) {
            return false;
        }

        if ($post['userId'] == $user['id']) {
            return true;
        }

        if ($this->getCourseService()->canManageCourse($post['courseId'])) {
            return true;
        }

        if ($this->getCourseMemberService()->isCourseMember($post['courseId'], $user['id'])) {
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
