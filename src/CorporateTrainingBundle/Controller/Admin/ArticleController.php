<?php

namespace CorporateTrainingBundle\Controller\Admin;

use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\Admin\ArticleController as BaseArticleController;

class ArticleController extends BaseArticleController
{
    public function indexAction(Request $request)
    {
        $conditions = $request->query->all();

        $categoryId = 0;

        if (!empty($conditions['categoryId'])) {
            $conditions['includeChildren'] = true;
            $categoryId = $conditions['categoryId'];
        }

        $paginator = new Paginator(
            $request,
            $this->getArticleService()->countArticles($conditions),
            20
        );

        $articles = $this->getArticleService()->searchArticles(
            $conditions,
            'normal',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $categoryIds = ArrayToolkit::column($articles, 'categoryId');
        $categories = $this->getCategoryService()->findCategoriesByIds($categoryIds);
        $categoryTree = $this->getCategoryService()->getCategoryTree();

        return $this->render('admin/article/index.html.twig', array(
            'articles' => $articles,
            'categories' => $categories,
            'paginator' => $paginator,
            'categoryTree' => $categoryTree,
            'categoryId' => $categoryId,
        ));
    }
}
