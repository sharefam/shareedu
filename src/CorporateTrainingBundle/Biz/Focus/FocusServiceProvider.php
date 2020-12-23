<?php

namespace CorporateTrainingBundle\Biz\Focus;

use AppBundle\Extension\Extension;
use CorporateTrainingBundle\Biz\Focus\Strategy\Impl\LiveCourseFocusStrategyImpl;
use CorporateTrainingBundle\Biz\Focus\Strategy\Impl\OfflineActivityFocusStrategyImpl;
use CorporateTrainingBundle\Biz\Focus\Strategy\Impl\ProjectPlanFocusStrategyImpl;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class FocusServiceProvider extends Extension implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        $biz['focus_live_course'] = function ($biz) {
            return new LiveCourseFocusStrategyImpl($biz);
        };

        $biz['focus_project_plan'] = function ($biz) {
            return new ProjectPlanFocusStrategyImpl($biz);
        };

        $biz['focus_offline_activity'] = function ($biz) {
            return new OfflineActivityFocusStrategyImpl($biz);
        };
    }
}
