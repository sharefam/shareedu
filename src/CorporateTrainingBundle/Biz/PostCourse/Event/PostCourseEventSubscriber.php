<?php

namespace CorporateTrainingBundle\Biz\PostCourse\Event;

use AppBundle\Common\ArrayToolkit;
use Biz\System\Service\SettingService;
use Biz\User\Service\UserService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Queue\Service\QueueService;
use Codeages\PluginBundle\Event\EventSubscriber;
use CorporateTrainingBundle\Biz\PostCourse\Service\PostCourseService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PostCourseEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'course-set.delete' => 'onPostCoursesDelete',
            'batch.create.post_courses' => 'onBatchCreatePostCourses',
        );
    }

    public function onPostCoursesDelete(Event $event)
    {
        $courseSet = $event->getSubject();
        $this->getPostCourseService()->deletePostCoursesByCourseSetId($courseSet['id']);
    }

    public function onBatchCreatePostCourses(Event $event)
    {
        $content = $event->getSubject();
        $postId = $content['postId'];
        $courses = $content['courses'];

        $this->sendNotification($postId, $courses);
    }

    protected function sendNotification($postId, $courses)
    {
        $types = array();
        $mailNotification = $this->getSettingService()->get('mail_notification', array());
        $dingtalkNotification = $this->getSettingService()->get('dingtalk_notification', array());

        if (!empty($mailNotification['post_assign'])) {
            $types[] = 'email';
        }

        if (!empty($dingtalkNotification['post_course_add'])) {
            $types[] = 'dingtalk';
        }
        if (empty($types)) {
            return;
        }
        list($to, $content) = $this->buildNotificationData($postId, $courses);
        $this->getBiz()->offsetGet('notification_default')->send($types, $to, $content);
    }

    protected function buildNotificationData($postId, $courses)
    {
        $users = $this->getUserService()->searchUsers(
            array('postId' => $postId, 'locked' => 0),
            array('id' => 'ASC'),
            0,
            PHP_INT_MAX
        );

        $courseTitles = ArrayToolkit::column($courses, 'title');
        $courseTitlesString = implode('》、《', $courseTitles);
        $courseTitlesString = '《'.$courseTitlesString.'》';

        global $kernel;
        $url = $kernel->getContainer()->get('router')->generate('study_center_my_task_learning', array(), true);

        $to = array(
            'type' => 'user',
            'userIds' => ArrayToolkit::column($users, 'id'),
            'startNum' => 0,
            'perPageNum' => 20,
        );
        $content = array(
            'template' => 'post_course_add',
            'params' => array(
                'targetId' => $postId,
                'batch' => 'post_course'.$postId.time(),
                'num' => count($courses),
                'courseTitlesString' => $courseTitlesString,
                'url' => $url,
            ),
        );

        return array($to, $content);
    }

    /**
     * @return QueueService
     */
    protected function getQueueService()
    {
        return $this->getBiz()->service('Queue:QueueService');
    }

    /**
     * @return PostCourseService
     */
    protected function getPostCourseService()
    {
        return $this->getBiz()->service('CorporateTrainingBundle:PostCourse:PostCourseService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->getBiz()->service('CorporateTrainingBundle:User:UserService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }
}
