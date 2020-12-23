<?php

namespace CorporateTrainingBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use CorporateTrainingBundle\Biz\OfflineActivity\Service\Impl\CategoryServiceImpl;
use CorporateTrainingBundle\Biz\OfflineActivity\Service\MemberService;
use CorporateTrainingBundle\Biz\OfflineActivity\Service\OfflineActivityService;

class MeOfflineActivityRecord extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $user = $this->getCurrentUser();
        $total = $this->getOfflineActivityMemberService()->countMembers(array('userId' => $user['id']));

        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $offlineActivityMembers = $this->getOfflineActivityMemberService()->searchMembers(
            array('userId' => $user['id']),
            array('id' => 'DESC'),
            $offset,
            $limit
        );

        $offlineActivityMembers = ArrayToolkit::index($offlineActivityMembers, 'offlineActivityId');
        $offlineActivities = $this->getOfflineActivityService()->findOfflineActivitiesByIds(ArrayToolkit::column($offlineActivityMembers, 'offlineActivityId'));
        $offlineActivities = ArrayToolkit::index($offlineActivities, 'id');
        $categoryIds = ArrayToolkit::column($offlineActivities, 'categoryId');
        $categories = $this->getCategoryService()->findCategoriesByIds($categoryIds);
        $categories = ArrayToolkit::index($categories, 'id');

        foreach ($offlineActivityMembers as $key => $member) {
            $offlineActivity = empty($offlineActivities[$member['offlineActivityId']]) ? array() : $offlineActivities[$member['offlineActivityId']];
            $offlineActivityMembers[$key]['offlineActivityName'] = empty($offlineActivity) ? '' : $offlineActivity['title'];
            $offlineActivityMembers[$key]['offlineActivityPlace'] = empty($offlineActivity) ? '' : $offlineActivity['address'];
            $offlineActivityMembers[$key]['startTime'] = empty($offlineActivity) ? '' : $offlineActivity['startTime'];
            $offlineActivityMembers[$key]['endTime'] = empty($offlineActivity) ? '' : $offlineActivity['endTime'];
            $offlineActivityMembers[$key]['categoryName'] = empty($offlineActivity) ? '' : $categories[$offlineActivity['categoryId']]['name'];
        }

        $this->getOCUtil()->multiple($offlineActivityMembers, array('userId'));

        return $this->makePagingObject(array_values($offlineActivityMembers), $total, $offset, $limit);
    }

    /**
     * @return OfflineActivityService
     */
    protected function getOfflineActivityService()
    {
        return $this->service('CorporateTrainingBundle:OfflineActivity:OfflineActivityService');
    }

    /**
     * @return CategoryServiceImpl
     */
    protected function getCategoryService()
    {
        return $this->service('CorporateTrainingBundle:OfflineActivity:CategoryService');
    }

    /**
     * @return MemberService
     */
    protected function getOfflineActivityMemberService()
    {
        return $this->service('CorporateTrainingBundle:OfflineActivity:MemberService');
    }
}
