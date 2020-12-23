<?php

namespace CorporateTrainingBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\BaseController;

class GuideController extends BaseController
{
    public function homePageGuideAction(Request $request)
    {
        $user = $this->getCurrentUser();

        $this->getUserService()->readGuide($user['id']);

        return $this->createJsonResponse(true);
    }

    protected function getUserService()
    {
        return $this->createService('CorporateTrainingBundle:User:UserService');
    }
}
