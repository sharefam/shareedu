<?php

namespace CorporateTrainingBundle\Biz\NotificationCenter;

use AppBundle\Extension\Extension;
use CorporateTrainingBundle\Biz\NotificationCenter\Strategy\Group\Impl\ClassroomGroup;
use CorporateTrainingBundle\Biz\NotificationCenter\Strategy\Group\Impl\CourseGroup;
use CorporateTrainingBundle\Biz\NotificationCenter\Strategy\Group\Impl\OfflineActivityGroup;
use CorporateTrainingBundle\Biz\NotificationCenter\Strategy\Group\Impl\PostGroup;
use CorporateTrainingBundle\Biz\NotificationCenter\Strategy\Group\Impl\ProjectPlanGroup;
use CorporateTrainingBundle\Biz\NotificationCenter\Strategy\Group\Impl\UserGroup;
use CorporateTrainingBundle\Biz\NotificationCenter\Strategy\Notification\Impl\DefaultNotificationStrategy;
use CorporateTrainingBundle\Biz\NotificationCenter\Strategy\Notification\Impl\DingTalkNotificationStrategy;
use CorporateTrainingBundle\Biz\NotificationCenter\Strategy\Notification\Impl\EmailNotificationStrategy;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class NotificationServiceProvider extends Extension implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        $biz['notification_default'] = function ($biz) {
            return new DefaultNotificationStrategy($biz);
        };

        $biz['notification_email'] = function ($biz) {
            return new EmailNotificationStrategy($biz);
        };

        $biz['notification_dingtalk'] = function ($biz) {
            return new DingTalkNotificationStrategy($biz);
        };

        $biz['notification_user_group_offline_activity'] = function ($biz) {
            return new OfflineActivityGroup($biz);
        };

        $biz['notification_user_group_project_plan'] = function ($biz) {
            return new ProjectPlanGroup($biz);
        };

        $biz['notification_user_group_user'] = function ($biz) {
            return new UserGroup($biz);
        };

        $biz['notification_user_group_post'] = function ($biz) {
            return new PostGroup($biz);
        };

        $biz['notification_user_group_course'] = function ($biz) {
            return new CourseGroup($biz);
        };

        $biz['notification_user_group_classroom'] = function ($biz) {
            return new ClassroomGroup($biz);
        };
    }
}
