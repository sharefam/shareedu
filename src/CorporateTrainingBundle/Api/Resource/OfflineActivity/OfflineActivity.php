<?php

namespace CorporateTrainingBundle\Api\Resource\OfflineActivity;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Taxonomy\Service\CategoryService;
use CorporateTrainingBundle\Biz\OfflineActivity\Service\OfflineActivityService;
use CorporateTrainingBundle\Biz\ResourceScope\Service\ResourceVisibleScopeService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OfflineActivity extends AbstractResource
{
    public function get(ApiRequest $request, $offlineActivityId)
    {
        $offlineActivity = $this->getOfflineActivityService()->getOfflineActivity($offlineActivityId);
        if (empty($offlineActivity)) {
            throw new NotFoundHttpException('线下活动不存在', null, ErrorCode::RESOURCE_NOT_FOUND);
        }
        $offlineActivity = $this->buildActivitiesInfo(array($offlineActivity));
        $offlineActivity = $this->buildUserOfflineActivities($offlineActivity);

        return array_shift($offlineActivity);
    }

    public function search(ApiRequest $request)
    {
        $searchType = $request->query->get('activityTimeStatus', 'ongoing');
        $conditions['searchType'] = ('end' === $searchType ? $searchType : 'ongoing');
        $conditions['status'] = 'published';
        $conditions['ids'] = $this->getResourceVisibleScopeService()->findVisibleResourceIdsByResourceTypeAndUserId('offlineActivity', $this->getCurrentUser()->getId());
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $searchOfflineActivities = $this->getOfflineActivityService()->searchOfflineActivities(
            $conditions,
            array('enrollmentEndDate' => 'ASC'),
            $offset,
            $limit
        );
        $searchOfflineActivities = $this->buildActivitiesInfo($searchOfflineActivities);
        $searchOfflineActivities = $this->buildUserOfflineActivities($searchOfflineActivities);

        return $this->makePagingObject($searchOfflineActivities, count($searchOfflineActivities), $offset, $limit);
    }

    protected function buildUserOfflineActivities($offlineActivities)
    {
        foreach ($offlineActivities as &$activity) {
            $activity['applyStatus'] = $this->getOfflineActivityService()->getUserApplyStatus($activity['id'], $this->getCurrentUser()->getId());
//          TODO  兼容app页面字段，后期app修改后删除
            $activity['startDate'] = $activity['startTime'];
            $activity['endDate'] = $activity['endTime'];
        }

        return $offlineActivities;
    }

    protected function buildActivitiesInfo($offlineActivities)
    {
        $categoryIds = ArrayToolkit::column($offlineActivities, 'categoryId');
        $categories = $this->getCategoryService()->findCategoriesByIds($categoryIds);
        $categories = ArrayToolkit::index($categories, 'id');

        foreach ($offlineActivities as &$activity) {
            $activity['activityTimeStatus'] = $this->getActivityStatus($activity);
            $activity['categoryName'] = empty($categories[$activity['categoryId']]) ? '' : $categories[$activity['categoryId']]['name'];
        }

        return $offlineActivities;
    }

    protected function getActivityStatus($activity)
    {
        if (time() > $activity['endTime']) {
            return 'end';
        }

        if (time() > $activity['startTime'] && time() < $activity['endTime']) {
            return 'ongoing';
        }

        return 'notStart';
    }

    /**
     * @return OfflineActivityService
     */
    protected function getOfflineActivityService()
    {
        return $this->service('CorporateTrainingBundle:OfflineActivity:OfflineActivityService');
    }

    /**
     * @return CategoryService
     */
    protected function getCategoryService()
    {
        return $this->service('Taxonomy:CategoryService');
    }

    /**
     * @return ResourceVisibleScopeService
     */
    protected function getResourceVisibleScopeService()
    {
        return $this->service('ResourceScope:ResourceVisibleScopeService');
    }
}
