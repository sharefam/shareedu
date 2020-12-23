<?php

namespace CorporateTrainingBundle\Biz\Attachment;

use Codeages\Biz\Framework\Context\Biz;

class BaseAttachment
{
    protected $biz;

    protected $serviceContainer;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    public function getServiceContainer()
    {
        return $this->serviceContainer;
    }

    public function setServiceContainer($container)
    {
        return $this->serviceContainer = $container;
    }

    public function canPreview($fileId)
    {
        return $this->canOperate($fileId, 'preview');
    }

    public function canPlayer($fileId)
    {
        return $this->canOperate($fileId, 'player');
    }

    public function canDownload($fileId)
    {
        return $this->canOperate($fileId, 'download');
    }

    public function canDelete($fileId)
    {
        return  $this->canOperate($fileId, 'delete');
    }

    protected function getCurrentUser()
    {
        return $this->biz->offsetGet('user');
    }

    protected function createService($alias)
    {
        return $this->biz->service($alias);
    }

    protected function createDao($alias)
    {
        return $this->biz->dao($alias);
    }

    public function canOperate($fileId, $type = '')
    {
        return false;
    }

    /**
     * @return \Biz\File\Service\Impl\UploadFileServiceImpl
     */
    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }
}
