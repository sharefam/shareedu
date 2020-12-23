<?php

namespace CorporateTrainingBundle\Biz\AdvancedMemberSelect;

use AppBundle\Common\ArrayToolkit;
use CorporateTrainingBundle\Biz\OfflineActivity\Service\MemberService;
use CorporateTrainingBundle\Biz\OfflineActivity\Service\OfflineActivityService;

class OfflineActivitySelect extends AbstractMemberSelect
{
    protected $resourceType = 'offline_activity';

    public function canSelect($activityId)
    {
        if (empty($activityId)) {
            return false;
        }

        return $this->getOfflineActivityService()->canManageOfflineActivity($activityId);
    }

    public function becomeMember($targetId, $userIds)
    {
        if (empty($userIds)) {
            return true;
        }

        return $this->getOfflineActivityMemberService()->batchBecomeMember($targetId, $userIds);
    }

    protected function filterMembers($targetId, $userIds)
    {
        $members = $this->getOfflineActivityMemberService()->searchMembers(array('userIds' => $userIds, 'activityId' => $targetId), array(), 0, count($userIds), array('userId'));
        $existUserIds = ArrayToolkit::column($members, 'userId');

        return array_diff($userIds, $existUserIds);
    }

    protected function sendNotification($userIds, $targetId)
    {
        if (empty($userIds)) {
            return;
        }
        $dingtalkNotification = $this->getSettingService()->get('dingtalk_notification', array());

        if (!empty($dingtalkNotification['offline_activity_add_member'])) {
            $types[] = 'dingtalk';
        }
        if (empty($types)) {
            return;
        }

        global $kernel;
        $activity = $this->getOfflineActivityService()->getOfflineActivity($targetId);
        $url = $kernel->getContainer()->get('router')->generate('offline_activity_detail', array('id' => $activity['id']), true);

        $to = array(
            'type' => 'user',
            'userIds' => $userIds,
            'startNum' => 0,
            'perPageNum' => 20,
        );

        $content = array(
            'template' => 'offline_activity_add_member',
            'params' => array(
                'url' => $url,
                'batch' => 'offline_activity_add_member'.$activity['id'].time(),
                'targetId' => $activity['id'],
                'offlineActivityTitle' => $activity['title'],
                'imagePath' => empty($activity['cover']['large']) ? '' : $activity['cover']['large'],
            ),
        );

        $this->biz->offsetGet('notification_default')->send($types, $to, $content);
    }

    /**
     * @return OfflineActivityService
     */
    protected function getOfflineActivityService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineActivity:OfflineActivityService');
    }

    /**
     * @return MemberService
     */
    protected function getOfflineActivityMemberService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineActivity:MemberService');
    }
}
