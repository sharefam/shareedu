<?php

namespace CorporateTrainingBundle\Biz\Attachment;

class QAThreadPostAttachment extends BaseAttachment
{
    public function canDelete($fileId)
    {
        $user = $this->getCurrentUser();
        $fileUsed = $this->getUploadFileService()->getUseFile($fileId);
        if (empty($fileUsed)) {
            return true;
        }
        $qaThreadPost = $this->getThreadService()->getPost($fileUsed['targetId']);

        if (empty($qaThreadPost) || 'qa.thread.post' != $fileUsed['targetType']) {
            return false;
        }

        if ($user['id'] == $qaThreadPost['userId']) {
            return true;
        }

        return false;
    }

    public function canOperate($fileId, $type = '')
    {
        $fileUsed = $this->getUploadFileService()->getUseFile($fileId);
        $qaThreadPost = $this->getThreadService()->getPost($fileUsed['targetId']);

        if (empty($qaThreadPost) || 'qa.thread.post' != $fileUsed['targetType']) {
            return false;
        }

        return true;
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
