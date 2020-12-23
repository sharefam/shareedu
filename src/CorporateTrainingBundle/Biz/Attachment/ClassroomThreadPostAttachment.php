<?php

namespace CorporateTrainingBundle\Biz\Attachment;

class ClassroomThreadPostAttachment extends BaseAttachment
{
    public function canDelete($fileId)
    {
        $user = $this->getCurrentUser();
        $fileUsed = $this->getUploadFileService()->getUseFile($fileId);
        if (empty($fileUsed)) {
            return true;
        }

        $post = $this->getThreadService()->getPost($fileUsed['targetId']);

        if ('classroom.thread.post' != $fileUsed['targetType']) {
            return false;
        }

        if ($user['id'] == $post['userId']) {
            return true;
        }

        return false;
    }

    public function canOperate($fileId, $type = '')
    {
        $user = $this->getCurrentUser();
        $fileUsed = $this->getUploadFileService()->getUseFile($fileId);
        $post = $this->getThreadService()->getPost($fileUsed['targetId']);

        if ('classroom.thread.post' != $fileUsed['targetType']) {
            return false;
        }
        if ($user['id'] == $post['userId'] && 'download' == $type) {
            return true;
        }
        $classroom = $this->getClassroomService()->getClassroom($post['targetId']);
        if ('classroom' != $post['targetType']) {
            return false;
        }

        $member = $this->getClassroomService()->isClassroomStudent($classroom['id'], $user['id']);
        if ($classroom['showable'] || $member) {
            return true;
        }

        return false;
    }

    /**
     * @return \Biz\Thread\Service\Impl\ThreadServiceImpl
     */
    protected function getThreadService()
    {
        return $this->createService('Thread:ThreadService');
    }

    /**
     * @return \Biz\Classroom\Service\Impl\ClassroomServiceImpl
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }
}
