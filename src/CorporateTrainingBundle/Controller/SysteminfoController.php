<?php

namespace CorporateTrainingBundle\Controller;

use AppBundle\Controller\SysteminfoController as BaseController;
use CorporateTrainingBundle\System;
use Symfony\Component\HttpFoundation\Request;

class SysteminfoController extends BaseController
{
    public function indexAction(Request $request)
    {
        $version = $this->getParam($request, 'version', '1');

        $info = array(
            'version' => \AppBundle\System::VERSION,
            'ct_version' => System::CT_VERSION,
            'name' => $this->setting('site.name', ''),
            'mobileApiVersion' => $version,
            'mobileApiUrl' => $request->getSchemeAndHttpHost().'/mapi_v'.$version,
        );

        return $this->createJson($request, $info);
    }
}
