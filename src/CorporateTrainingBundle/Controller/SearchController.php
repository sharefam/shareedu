<?php

namespace CorporateTrainingBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\SearchController as BaseController;

class SearchController extends BaseController
{
    public function indexAction(Request $request)
    {
        $paginator = null;

        $keywords = $request->query->get('q');
        $keywords = $this->filterKeyWord(trim($keywords));
        $type = $request->query->get('type', 'course');

        $cloud_search_setting = $this->getSettingService()->get('cloud_search', array());

        if (isset($cloud_search_setting['search_enabled']) && $cloud_search_setting['search_enabled'] && 'ok' == $cloud_search_setting['status']) {
            return $this->redirect(
                $this->generateUrl(
                    'cloud_search',
                    array(
                        'q' => $keywords,
                        'type' => $request->query->get('type'),
                    )
                )
            );
        }

        if (empty($keywords)) {
            return $this->render(
                'ct-search/search-failure.html.twig',
                array(
                    'keywords' => $keywords,
                    'type' => $type,
                    'errorMessage' => $this->trans('search.search_failure.error.message'),
                )
            );
        }

        list($resultSets, $paginator) = $this->getSearchFunction($type)->search($request, $keywords);

        $resultNum = $this->searchResultNum($request, $keywords);

        return $this->render(
            'ct-search/index.html.twig',
            array(
                'resultSets' => $resultSets,
                'resultNum' => $resultNum,
                'paginator' => $paginator,
                'keywords' => $keywords,
                'type' => $type,
            )
        );
    }

    private function searchResultNum($request, $keywords)
    {
        $resultNum = array();
        $resultNum['course'] = $this->getSearchFunction('course')->count($request, $keywords);
        $resultNum['classroom'] = $this->getSearchFunction('classroom')->count($request, $keywords);
        $resultNum['projectPlan'] = $this->getSearchFunction('projectPlan')->count($request, $keywords);
        $resultNum['article'] = $this->getSearchFunction('article')->count($request, $keywords);
        $resultNum['offlineActivity'] = $this->getSearchFunction('offlineActivity')->count($request, $keywords);
        $resultNum['thread'] = $this->getSearchFunction('thread')->count($request, $keywords);
        $resultNum['group'] = $this->getGroupService()->searchGroupsCount(array('title' => $keywords));
        $resultNum['groupThread'] = $resultNum['thread'] + $resultNum['group'];

        return $resultNum;
    }

    private function filterKeyWord($keyword)
    {
        $keyword = str_replace('<', '', $keyword);
        $keyword = str_replace('>', '', $keyword);
        $keyword = str_replace("'", '', $keyword);
        $keyword = str_replace('"', '', $keyword);
        $keyword = str_replace('=', '', $keyword);
        $keyword = str_replace('&', '', $keyword);
        $keyword = str_replace('/', '', $keyword);

        return $keyword;
    }

    protected function getSearchFunction($type)
    {
        $searchFactory = $this->getBiz()->offsetGet('search_factory');

        return $searchFactory->create($type);
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\Impl\ProjectPlanServiceImpl
     */
    protected function getProjectPlanService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:ProjectPlanService');
    }

    /**
     * @return \Biz\Article\Service\Impl\ArticleServiceImpl
     */
    protected function getArticleService()
    {
        return $this->createService('Article:ArticleService');
    }

    /**
     * @return \Biz\Group\Service\Impl\ThreadServiceImpl
     */
    protected function getThreadService()
    {
        return $this->createService('Group:ThreadService');
    }

    /**
     * @return \Biz\Group\Service\Impl\GroupServiceImpl
     */
    protected function getGroupService()
    {
        return $this->createService('Group:GroupService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineActivity\Service\Impl\OfflineActivityServiceImpl
     */
    protected function getOfflineActivityService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineActivity:OfflineActivityService');
    }

    /**
     * @return \Biz\Task\Service\Impl\TaskServiceImpl
     */
    protected function getCourseTaskService()
    {
        return $this->createService('Task:TaskService');
    }
}
