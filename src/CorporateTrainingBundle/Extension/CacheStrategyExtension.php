<?php

namespace CorporateTrainingBundle\Extension;

use CorporateTrainingBundle\Common\BizFramework\CacheStrategy\TableJoinQueryStrategy;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class CacheStrategyExtension extends Extension implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        $biz['dao.cache.strategy.tablejoin'] = function ($biz) {
            return new TableJoinQueryStrategy($biz);
        };
    }
}
