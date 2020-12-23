<?php

namespace CorporateTrainingBundle\Extension;

use Codeages\Biz\Framework\Context\BizAware;

abstract class Extension extends BizAware
{
    public function getProjectPlanItems()
    {
        return array();
    }

    public function getTrainingActivities()
    {
        return array();
    }

    public function getOrgModules()
    {
        return array();
    }

    public function getUserLearnDataModules()
    {
        return array();
    }
}
