<?php

namespace CorporateTrainingBundle\Biz\AdvancedMemberSelect;

use AppBundle\Common\ArrayToolkit;
use CorporateTrainingBundle\Biz\Course\Service\CourseSetService;

class CourseMemberSelect extends AbstractMemberSelect
{
    protected $resourceType = 'course_member';

    public function canSelect($targetId)
    {
        if (empty($targetId)) {
            return false;
        }

        return $this->getCourseService()->canManageCourse($targetId);
    }

    public function becomeMember($targetId, $userIds)
    {
        if (empty($userIds)) {
            return true;
        }

        return $this->getCourseMemberService()->batchBecomeStudents($targetId, $userIds);
    }

    protected function filterMembers($targetId, $userIds)
    {
        $members = $this->getCourseMemberService()->searchMembers(array('userIds' => $userIds, 'courseId' => $targetId), array(), 0, count($userIds), array('userId'));
        $existUserIds = ArrayToolkit::column($members, 'userId');

        return array_diff($userIds, $existUserIds);
    }

    protected function sendNotification($userIds, $targetId)
    {
        if (!empty($this->dingtalkNotification['online_course_assign'])) {
            $types[] = 'dingtalk';
        }
        if (empty($types)) {
            return;
        }

        global $kernel;
        $url = $kernel->getContainer()->get('router')->generate('course_show', array('id' => $targetId), true);
        $course = $this->getCourseService()->getCourse($targetId);
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);

        $to = array(
            'type' => 'user',
            'userIds' => $userIds,
            'startNum' => 0,
            'perPageNum' => 20,
        );
        $content = array(
            'template' => 'online_course_assign',
            'params' => array(
                'targetId' => $course['id'],
                'batch' => 'online_course_assign'.$course['id'].time(),
                'courseTitle' => $courseSet['title'],
                'url' => $url,
                'imagePath' => empty($courseSet['cover']['large']) ? '' : $courseSet['cover']['large'],
            ),
        );

        $this->biz->offsetGet('notification_default')->send($types, $to, $content);
    }

    /**
     * @return \CorporateTrainingBundle\Biz\Course\Service\Impl\CourseServiceImpl
     */
    protected function getCourseService()
    {
        return $this->createService('CorporateTrainingBundle:Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('CorporateTrainingBundle:Course:CourseSetService');
    }

    /**
     * @return \Biz\Course\Service\Impl\MemberServiceImpl
     */
    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }
}
