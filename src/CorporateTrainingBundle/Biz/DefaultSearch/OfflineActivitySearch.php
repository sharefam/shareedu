<?php

namespace CorporateTrainingBundle\Biz\DefaultSearch;

use AppBundle\Common\Paginator;

class OfflineActivitySearch extends AbstractSearch
{
    public function search($request, $keywords)
    {
        $conditions = $this->prepareSearchConditions($keywords);

        $offlineActivityNum = $this->getOfflineActivityService()->countOfflineActivities($conditions);

        $paginator = new Paginator(
            $request,
            $offlineActivityNum,
            10
        );

        $offlineActivities = $this->getOfflineActivityService()->searchOfflineActivities(
            $conditions,
            array('updatedTime' => 'desc'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return array($offlineActivities, $paginator);
    }

    public function count($request, $keywords)
    {
        $conditions = $this->prepareSearchConditions($keywords);

        $offlineActivityNum = $this->getOfflineActivityService()->countOfflineActivities($conditions);

        return $offlineActivityNum;
    }

    protected function prepareSearchConditions($keywords)
    {
        $user = $this->getCurrentUser();
        $scopeOfflineActivityIds = $this->getResourceVisibleScopeService()->findVisibleResourceIdsByResourceTypeAndUserId('offlineActivity',
            $user['id']);
        $conditions = array(
            'ids' => $scopeOfflineActivityIds,
            'status' => 'published',
            'title' => $keywords,
        );

        return $conditions;
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineActivity\Service\Impl\OfflineActivityServiceImpl
     */
    protected function getOfflineActivityService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineActivity:OfflineActivityService');
    }
}
