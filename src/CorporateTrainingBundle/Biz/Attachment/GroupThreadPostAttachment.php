<?php

namespace CorporateTrainingBundle\Biz\Attachment;

class GroupThreadPostAttachment extends BaseAttachment
{
    public function canDelete($fileId)
    {
        $user = $this->getCurrentUser();
        $fileUsed = $this->getUploadFileService()->getUseFile($fileId);
        if (empty($fileUsed)) {
            return true;
        }
        $post = $this->getThreadService()->getPost($fileUsed['targetId']);

        if (empty($post) || 'group.thread.post' != $fileUsed['targetType']) {
            return false;
        }

        if ($user['id'] == $post['userId']) {
            return true;
        }

        return false;
    }

    public function canOperate($fileId, $type = '')
    {
        $fileUsed = $this->getUploadFileService()->getUseFile($fileId);
        $post = $this->getThreadService()->getPost($fileUsed['targetId']);

        if (empty($post) || 'group.thread.post' != $fileUsed['targetType']) {
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
