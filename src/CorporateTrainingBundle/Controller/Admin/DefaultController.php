<?php

namespace CorporateTrainingBundle\Controller\Admin;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Controller\Admin\DefaultController as BaseController;

class DefaultController extends BaseController
{
    public function systemStatusAction()
    {
        $apps = $this->getAppService()->checkAppUpgrades();

        $upgradeAppCount = count($apps);

        $indexApps = ArrayToolkit::index($apps, 'code');
        $mainAppUpgrade = empty($indexApps['TRAININGMAIN']) ? array() : $indexApps['TRAININGMAIN'];

        if ($mainAppUpgrade) {
            $upgradeAppCount = $upgradeAppCount - 1;
        }

        return $this->render('admin/default/system-status.html.twig', array(
            'mainAppUpgrade' => $mainAppUpgrade,
            'upgradeAppCount' => $upgradeAppCount,
            'disabledCloudServiceCount' => $this->getDisabledCloudServiceCount(),
        ));
    }
}
