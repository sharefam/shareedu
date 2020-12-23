<?php

namespace CorporateTrainingBundle\Biz\DefaultSearch;

use AppBundle\Common\Paginator;

class ArticleSearch extends AbstractSearch
{
    public function search($request, $keywords)
    {
        $conditions = array(
            'keywords' => $keywords,
        );

        $articleNum = $this->getArticleService()->countArticles($conditions);

        $paginator = new Paginator(
            $request,
            $articleNum,
            10
        );

        $articles = $this->getArticleService()->searchArticles(
            $conditions,
            array('updatedTime' => 'desc'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return array($articles, $paginator);
    }

    public function count($request, $keywords)
    {
        $conditions = array(
            'keywords' => $keywords,
        );

        $articleNum = $this->getArticleService()->countArticles($conditions);

        return $articleNum;
    }

    /**
     * @return \Biz\Article\Service\Impl\ArticleServiceImpl
     */
    protected function getArticleService()
    {
        return $this->createService('Article:ArticleService');
    }
}
