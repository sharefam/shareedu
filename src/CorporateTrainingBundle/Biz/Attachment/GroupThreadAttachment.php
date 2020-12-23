<?php

namespace CorporateTrainingBundle\Biz\Attachment;

class GroupThreadAttachment extends BaseAttachment
{
    public function canDelete($fileId)
    {
        $user = $this->getCurrentUser();
        $fileUsed = $this->getUploadFileService()->getUseFile($fileId);
        if (empty($fileUsed)) {
            return true;
        }
        $thread = $this->getThreadService()->getThread($fileUsed['targetId']);

        if (empty($thread) || 'group.thread' != $fileUsed['targetType']) {
            return false;
        }

        if ($user['id'] == $thread['userId']) {
            return true;
        }

        return false;
    }

    public function canOperate($fileId, $type = '')
    {
        $fileUsed = $this->getUploadFileService()->getUseFile($fileId);
        $thread = $this->getThreadService()->getThread($fileUsed['targetId']);

        if (empty($thread) || 'group.thread' != $fileUsed['targetType']) {
            return false;
        }

        return true;
    }

    /**
     * @return \Biz\Group\Service\Impl\ThreadServiceImpl
     */
    protected function getThreadService()
    {
        return $this->createService('Group:ThreadService');
    }
}
