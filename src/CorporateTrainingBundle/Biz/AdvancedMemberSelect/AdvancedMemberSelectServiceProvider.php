<?php

namespace CorporateTrainingBundle\Biz\AdvancedMemberSelect;

use AppBundle\Extension\Extension;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class AdvancedMemberSelectServiceProvider extends Extension implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        $this->registerExportType($biz);
        $biz['advanced_member_select_factory'] = function ($biz) {
            $exporter = new MemberSelectFactory();
            $exporter->setBiz($biz);

            return $exporter;
        };
    }

    protected function registerExportType($biz)
    {
        $biz['advanced_member_select.project_plan_member'] = function ($biz) {
            return new ProjectPlanMemberSelect($biz);
        };

        $biz['advanced_member_select.offlineActivity_member'] = function ($biz) {
            return new OfflineActivitySelect($biz);
        };

        $biz['advanced_member_select.classroom_member'] = function ($biz) {
            return new ClassroomMemberSelect($biz);
        };

        $biz['advanced_member_select.course_member'] = function ($biz) {
            return new CourseMemberSelect($biz);
        };
    }
}
