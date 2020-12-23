<?php

namespace CorporateTrainingBundle\Biz\AdvancedMemberSelect;

use AppBundle\Common\ArrayToolkit;

class ClassroomMemberSelect extends AbstractMemberSelect
{
    protected $resourceType = 'classroom_member';

    public function canSelect($targetId)
    {
        if (empty($targetId)) {
            return false;
        }

        return $this->getClassroomService()->canManageClassroom($targetId);
    }

    public function becomeMember($targetId, $userIds)
    {
        if (empty($userIds)) {
            return true;
        }

        return $this->getClassroomService()->batchBecomeStudent($targetId, $userIds);
    }

    protected function filterMembers($targetId, $userIds)
    {
        $members = $this->getClassroomService()->searchMembers(array('userIds' => $userIds, 'classroomId' => $targetId), array(), 0, count($userIds), array('userId'));
        $existUserIds = ArrayToolkit::column($members, 'userId');

        return array_diff($userIds, $existUserIds);
    }

    protected function sendNotification($userIds, $targetId)
    {
        if (!empty($this->dingtalkNotification['classroom_assign'])) {
            $types[] = 'dingtalk';
        }
        if (empty($types)) {
            return;
        }

        global $kernel;
        $url = $kernel->getContainer()->get('router')->generate('classroom_introductions', array('id' => $targetId), true);
        $classroom = $this->getClassroomService()->getClassroom($targetId);
        $to = array(
            'type' => 'user',
            'userIds' => $userIds,
            'startNum' => 0,
            'perPageNum' => 20,
        );
        $content = array(
            'template' => 'classroom_assign',
            'params' => array(
                'targetId' => $classroom['id'],
                'batch' => 'classroom_assign'.$classroom['id'].time(),
                'classroomTitle' => $classroom['title'],
                'url' => $url,
                'imagePath' => $classroom['middlePicture'],
            ),
        );

        $this->biz->offsetGet('notification_default')->send($types, $to, $content);
    }

    /**
     * @return \Biz\Classroom\Service\Impl\ClassroomServiceImpl
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }
}
