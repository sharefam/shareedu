<?php

namespace CorporateTrainingBundle\Biz\Article\Service\Impl;

use Biz\Article\Service\Impl\ArticleServiceImpl as BaseArticleService;

class ArticleServiceImpl extends BaseArticleService
{
    public function initOrgsRelation()
    {
        $fields = array('1', '1.');

        return $this->getArticleDao()->initOrgsRelation($fields);
    }
}
