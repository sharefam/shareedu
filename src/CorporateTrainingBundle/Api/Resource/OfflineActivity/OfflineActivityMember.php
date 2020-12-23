<?php

namespace CorporateTrainingBundle\Api\Resource\OfflineActivity;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use CorporateTrainingBundle\Biz\OfflineActivity\Service\MemberService;
use CorporateTrainingBundle\Biz\OfflineActivity\Service\OfflineActivityService;
use CorporateTrainingBundle\Biz\Post\Service\PostService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OfflineActivityMember extends AbstractResource
{
    public function get(ApiRequest $request, $offlineActivityId, $userId)
    {
        $member = $this->getOfflineActivityMemberService()->getMemberByActivityIdAndUserId($offlineActivityId, $userId);
        $this->getOCUtil()->multiple($member, array('userId'));

        return $member;
    }

    public function search(ApiRequest $request, $offlineActivityId)
    {
        $conditions['activityId'] = $offlineActivityId;
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $members = $this->getOfflineActivityMemberService()->searchMembers(
            $conditions,
            array('createdTime' => 'DESC'),
            $offset,
            $limit
        );

        $this->getOCUtil()->multiple($members, array('userId'));
        $postIds = array();
        foreach ($members as $member) {
            $postIds[] = $member['user']['postId'];
        }
        $posts = $this->getPostService()->findPostsByIds($postIds);
        $posts = ArrayToolkit::index($posts, 'id');
        foreach ($members as &$member) {
            if (isset($member['user']['postId'])) {
                $member['user']['postName'] = empty($posts[$member['user']['postId']]) ? '' : $posts[$member['user']['postId']]['name'];
            }
        }

        return $this->makePagingObject($members, count($members), $offset, $limit);
    }

    public function add(ApiRequest $request, $offlineActivityId)
    {
        $offlineActivity = $this->getOfflineActivityService()->getOfflineActivity($offlineActivityId);
        if (empty($offlineActivity)) {
            throw  new NotFoundHttpException('线下活动不存在', null, ErrorCode::RESOURCE_NOT_FOUND);
        }

        if (empty($offlineActivity['requireAudit'])) {
            try {
                $result = $this->getOfflineActivityMemberService()->becomeMember($offlineActivityId, $this->getCurrentUser()->getId());
            } catch (\Exception $e) {
                $result = false;
            }
        } else {
            $result = $this->getOfflineActivityService()->applyAttendOfflineActivity($offlineActivityId, $this->getCurrentUser()->getId());
        }

        return array('result' => empty($result) ? false : true);
    }

    /**
     * @return OfflineActivityService
     */
    protected function getOfflineActivityService()
    {
        return $this->service('CorporateTrainingBundle:OfflineActivity:OfflineActivityService');
    }

    /**
     * @return MemberService
     */
    protected function getOfflineActivityMemberService()
    {
        return $this->service('CorporateTrainingBundle:OfflineActivity:MemberService');
    }

    /**
     * @return PostService
     */
    protected function getPostService()
    {
        return $this->service('CorporateTrainingBundle:Post:PostService');
    }
}
