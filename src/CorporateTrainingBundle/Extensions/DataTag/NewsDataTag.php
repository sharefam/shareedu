<?php

namespace CorporateTrainingBundle\Extensions\DataTag;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Extensions\DataTag\DataTag;
use AppBundle\Extensions\DataTag\BaseDataTag;

class NewsDataTag extends BaseDataTag implements DataTag
{
    public function getData(array $arguments)
    {
        $articles = $this->getArticleService()->searchArticles(
            array('featuredNotEqual' => 1, 'status' => 'published'),
            array('publishedTime' => 'DESC'),
            0, $arguments['count']
        );

        return $this->buildCategories($articles);
    }

    protected function buildCategories($articles)
    {
        $categoryIds = ArrayToolkit::column($articles, 'categoryId');
        $categories = $this->getCategoryService()->findCategoriesByIds($categoryIds);

        foreach ($articles as &$article) {
            if (isset($categories[$article['categoryId']])) {
                $article['categoryName'] = $categories[$article['categoryId']]['name'];
            } else {
                $article['categoryName'] = array();
            }
        }

        return $articles;
    }

    protected function getArticleService()
    {
        return $this->getServiceKernel()->getBiz()->service('Article:ArticleService');
    }

    protected function getCategoryService()
    {
        return $this->getServiceKernel()->getBiz()->service('Article:CategoryService');
    }
}
