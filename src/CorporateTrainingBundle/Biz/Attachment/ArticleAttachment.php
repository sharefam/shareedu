<?php

namespace CorporateTrainingBundle\Biz\Attachment;

class ArticleAttachment extends BaseAttachment
{
    public function canDelete($fileId)
    {
        $user = $this->getCurrentUser();
        $fileUsed = $this->getUploadFileService()->getUseFile($fileId);
        if (empty($fileUsed)) {
            return true;
        }
        $article = $this->getArticleService()->getArticle($fileUsed['targetId']);

        if (empty($article) || 'article' != $fileUsed['targetType']) {
            return false;
        }

        if ($user['id'] == $article['userId']) {
            return true;
        }

        return false;
    }

    public function canOperate($fileId, $type = '')
    {
        $fileUsed = $this->getUploadFileService()->getUseFile($fileId);
        $article = $this->getArticleService()->getArticle($fileUsed['targetId']);

        if (empty($article) || 'article' != $fileUsed['targetType']) {
            return false;
        }

        return true;
    }

    /**
     * @return \Biz\Article\Service\Impl\ArticleServiceImpl
     */
    protected function getArticleService()
    {
        return $this->createService('Article:ArticleService');
    }
}
