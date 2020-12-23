<?php

namespace CorporateTrainingBundle\Extension;

use CorporateTrainingBundle\Biz\ResourceScope\Strategy\Impl\HireDateStrategyImpl;
use CorporateTrainingBundle\Biz\ResourceScope\Strategy\Impl\OrgStrategyImpl;
use CorporateTrainingBundle\Biz\ResourceScope\Strategy\Impl\PostStrategyImpl;
use CorporateTrainingBundle\Biz\ResourceScope\Strategy\Impl\UserGroupStrategyImpl;
use CorporateTrainingBundle\Biz\ResourceScope\Strategy\ResourceScopeStrategyContext;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ResourceScopeExtension extends Extension implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        $biz['resource_scope_strategy_context'] = function ($biz) {
            return new ResourceScopeStrategyContext($biz);
        };

        $this->registerOrg($biz);
        $this->registerPost($biz);
        $this->registerUserGroup($biz);
        $this->registerHireDate($biz);
    }

    protected function registerOrg(Container $biz)
    {
        $biz->extend('resource_scope_strategy_context', function ($context, $biz) {
            $context->addVisibleScopeType('Org');
            $context->addAccessScopeType('Org');
            return $context;
        });

        $biz['resource_scope_strategy_Org'] = function ($biz) {
            return new OrgStrategyImpl($biz);
        };
    }

    protected function registerPost(Container $biz)
    {
        $biz->extend('resource_scope_strategy_context', function ($context, $biz) {
            $context->addVisibleScopeType('Post');
            $context->addAccessScopeType('Post');
            return $context;
        });

        $biz['resource_scope_strategy_Post'] = function ($biz) {
            return new PostStrategyImpl($biz);
        };
    }

    protected function registerUserGroup(Container $biz)
    {
        $biz->extend('resource_scope_strategy_context', function ($context, $biz) {
            $context->addVisibleScopeType('UserGroup');
            $context->addAccessScopeType('UserGroup');
            return $context;
        });

        $biz['resource_scope_strategy_UserGroup'] = function ($biz) {
            return new UserGroupStrategyImpl($biz);
        };
    }

    protected function registerHireDate(Container $biz)
    {
        $biz->extend('resource_scope_strategy_context', function ($context, $biz) {
            $context->addAccessScopeType('HireDate');
            return $context;
        });

        $biz['resource_scope_strategy_HireDate'] = function ($biz) {
            return new HireDateStrategyImpl($biz);
        };
    }
}
