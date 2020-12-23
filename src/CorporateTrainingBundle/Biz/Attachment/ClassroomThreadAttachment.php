<?php

namespace CorporateTrainingBundle\Biz\Attachment;

class ClassroomThreadAttachment extends BaseAttachment
{
    public function canDelete($fileId)
    {
        $user = $this->getCurrentUser();
        $fileUsed = $this->getUploadFileService()->getUseFile($fileId);
        if (empty($fileUsed)) {
            return true;
        }

        $thread = $this->getThreadService()->getThread($fileUsed['targetId']);

        if ('classroom.thread' != $fileUsed['targetType']) {
            return false;
        }

        if ($user['id'] == $thread['userId']) {
            return true;
        }

        return false;
    }

    public function canOperate($fileId, $type = '')
    {
        $user = $this->getCurrentUser();
        $fileUsed = $this->getUploadFileService()->getUseFile($fileId);
        $thread = $this->getThreadService()->getThread($fileUsed['targetId']);

        if ('classroom.thread' != $fileUsed['targetType']) {
            return false;
        }
        if ($user['id'] == $thread['userId'] && 'download' == $type) {
            return true;
        }

        $classroom = $this->getClassroomService()->getClassroom($thread['targetId']);
        if ('classroom' != $thread['targetType']) {
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
