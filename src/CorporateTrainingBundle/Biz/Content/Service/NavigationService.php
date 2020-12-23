<?php

namespace CorporateTrainingBundle\Biz\Content\Service;

use Biz\Content\Service\NavigationService as BaseService;

interface NavigationService extends BaseService
{
    public function initOrgsRelation();
}
