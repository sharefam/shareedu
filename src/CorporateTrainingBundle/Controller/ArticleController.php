<?php

namespace CorporateTrainingBundle\Controller;

use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\ArticleController as BaseArticleController;

class ArticleController extends BaseArticleController
{
    public function indexAction(Request $request)
    {
        $categoryTree = $this->getCategoryService()->getCategoryTree();
        $conditions = array('status' => 'published');

        $paginator = new Paginator(
            $this->get('request'),
            $this->getArticleService()->countArticles($conditions),
            $this->setting('article.pageNums', 10)
        );

        $latestArticles = $this->getArticleService()->searchArticles(
            $conditions,
            'published',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $categoryIds = ArrayToolkit::column($latestArticles, 'categoryId');

        $categories = $this->getCategoryService()->findCategoriesByIds($categoryIds);

        $featuredConditions = array(
            'status' => 'published',
            'featured' => 1,
        );

        $featuredArticles = $this->getArticleService()->searchArticles(
            $featuredConditions,
            'normal',
            0,
            5
        );

        $featuredCategories = array();

        foreach ($featuredArticles as $key => $value) {
            $featuredCategories[$value['id']] = $this->getCategoryService()->getCategory($value['categoryId']);
        }

        $promotedConditions = $this->fillOrgCode(
            array(
                'status' => 'published',
                'promoted' => 1,
            )
        );

        $promotedArticles = $this->getArticleService()->searchArticles(
            $promotedConditions,
            'normal',
            0,
            2
        );

        $promotedCategories = array();

        foreach ($promotedArticles as $key => $value) {
            $promotedCategories[$value['id']] = $this->getCategoryService()->getCategory($value['categoryId']);
        }

        return $this->render(
            'article/index.html.twig',
            array(
                'categoryTree' => $categoryTree,
                'latestArticles' => $latestArticles,
                'featuredArticles' => $featuredArticles,
                'featuredCategories' => $featuredCategories,
                'promotedArticles' => $promotedArticles,
                'promotedCategories' => $promotedCategories,
                'paginator' => $paginator,
                'categories' => $categories,
            )
        );
    }

    public function popularArticlesBlockAction()
    {
        $conditions = array(
                'type' => 'article',
                'status' => 'published',
        );
        $articles = $this->getArticleService()->searchArticles($conditions, 'popular', 0, 5);

        return $this->render(
            'article/popular-articles-block.html.twig',
            array(
                'articles' => $articles,
            )
        );
    }
}
