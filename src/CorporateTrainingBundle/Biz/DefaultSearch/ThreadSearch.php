<?php

namespace CorporateTrainingBundle\Biz\DefaultSearch;

use AppBundle\Common\Paginator;

class ThreadSearch extends AbstractSearch
{
    public function search($request, $keywords)
    {
        $conditions = array(
            'title' => $keywords,
        );

        $threadNum = $this->getThreadService()->countThreads($conditions);

        $paginator = new Paginator(
            $request,
            $threadNum,
            10
        );

        $threads = $this->getThreadService()->searchThreads(
            $conditions,
            array('lastPostTime' => 'desc'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return array($threads, $paginator);
    }

    public function count($request, $keywords)
    {
        $conditions = array(
            'title' => $keywords,
        );

        $threadNum = $this->getThreadService()->countThreads($conditions);

        return $threadNum;
    }

    /**
     * @return \Biz\Group\Service\Impl\ThreadServiceImpl
     */
    protected function getThreadService()
    {
        return $this->createService('Group:ThreadService');
    }
}
