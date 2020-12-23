<?php

namespace Biz\Testpaper\Event;

use AppBundle\Common\ArrayToolkit;
use Biz\System\Service\SettingService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TestpaperEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'exam.finish' => 'onTestpaperFinish',
            'exam.reviewed' => 'onTestpaperReviewd',
        );
    }

    public function onTestpaperFinish(Event $event)
    {
        $paperResult = $event->getSubject();

        $biz = $this->getBiz();
        $user = $biz['user'];
        $userProfile = $this->getUserService()->getUserProfile($user['id']);

        $itemCount = $this->getTestpaperService()->searchItemCount(array(
            'testId' => $paperResult['testId'],
            'questionTypes' => array('essay'),
        ));

        if ($itemCount) {
            $course = $this->getCourseService()->getCourse($paperResult['courseId']);

            $message = array(
                'id' => $paperResult['id'],
                'courseId' => $paperResult['courseId'],
                'name' => $paperResult['paperName'],
                'userId' => $user['id'],
                'userName' => !empty($userProfile['truename']) ? $userProfile['truename'] : $user['nickname'],
                'testpaperType' => $paperResult['type'],
                'type' => 'perusal',
            );

            if (!empty($course['teacherIds'])) {
                foreach ($course['teacherIds'] as $receiverId) {
                    $result = $this->getNotificationService()->notify($receiverId, 'test-paper', $message);
                }
            }
        }
    }

    public function onTestpaperReviewd(Event $event)
    {
        $paperResult = $event->getSubject();

        $biz = $this->getBiz();
        $user = $biz['user'];
        $userProfile = $this->getUserService()->getUserProfile($user['id']);

        $itemResults = $this->getTestpaperService()->findItemResultsByResultId($paperResult['id']);
        $itemResults = ArrayToolkit::group($itemResults, 'status');

        if (!empty($itemResults['right'])) {
            $rightItemCount = count($itemResults['right']);
            $this->getTestpaperService()->updateTestpaperResult($paperResult['id'], array('rightItemCount' => $rightItemCount));
        }

        if (!in_array($paperResult['type'], array('testpaper', 'homework'))) {
            return;
        }

        $message = array(
            'id' => $paperResult['id'],
            'courseId' => $paperResult['courseId'],
            'name' => $paperResult['paperName'],
            'userId' => $user['id'],
            'userName' => !empty($userProfile['truename']) ? $userProfile['truename'] : $user['nickname'],
            'type' => 'read',
            'testpaperType' => $paperResult['type'],
        );
        $this->sendNotification(array($paperResult['userId']), $paperResult);
        $result = $this->getNotificationService()->notify($paperResult['userId'], 'test-paper', $message);
    }

    protected function sendNotification($userIds, $paperResult)
    {
        if (empty($userIds) || 'testpaper' != $paperResult['type']) {
            return;
        }
        $dingtalkNotification = $this->getSettingService()->get('dingtalk_notification', array());

        if (!empty($dingtalkNotification['online_course_exam_result'])) {
            $types[] = 'dingtalk';
        }
        if (empty($types)) {
            return;
        }

        global $kernel;
        $url = $kernel->getContainer()->get('router')->generate('testpaper_result_show', array('resultId' => $paperResult['id']), true);
        $testpaper = $this->getTestpaperService()->getTestpaper($paperResult['testId']);

        $to = array(
            'type' => 'user',
            'userIds' => $userIds,
            'startNum' => 0,
            'perPageNum' => 20,
        );

        $content = array(
            'template' => 'online_course_exam_result',
            'params' => array(
                'batch' => 'online_course_exam_result'.$paperResult['id'].time(),
                'targetId' => $paperResult['id'],
                'title' => $paperResult['paperName'],
                'url' => $url,
                'score' => $paperResult['score'],
                'totalScore' => $testpaper['score'],
                'status' => ('passed' == $paperResult['passedStatus']) ? '通过' : '未通过',
            ),
        );

        $this->getBiz()->offsetGet('notification_default')->send($types, $to, $content);
    }

    /**
     * @return \Biz\User\Service\Impl\UserServiceImpl
     */
    public function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }

    /**
     * @return \Biz\Testpaper\Service\TestpaperService
     */
    public function getTestpaperService()
    {
        return $this->getBiz()->service('Testpaper:TestpaperService');
    }

    public function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    public function getNotificationService()
    {
        return $this->getBiz()->service('User:NotificationService');
    }

    public function getClassroomService()
    {
        return $this->getBiz()->service('Classroom:ClassroomService');
    }

    public function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }

    public function getStatusService()
    {
        return $this->getBiz()->service('User:StatusService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }
}
