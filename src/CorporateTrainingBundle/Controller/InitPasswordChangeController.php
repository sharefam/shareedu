<?php

namespace CorporateTrainingBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\BaseController;

class InitPasswordChangeController extends BaseController
{
    public function initAction(Request $request)
    {
        $user = $this->getCurrentUser();

        $siteInfo = $this->getSettingService()->get('site_info');
        if (in_array('ROLE_SUPER_ADMIN', $user['roles']) && (empty($siteInfo) || empty($siteInfo['status']))) {
            return $this->render(
                'login/admin-login.html.twig'
            );
        }

        return $this->render(
            'init-password-change/index.html.twig'
        );
    }

    public function changePasswordAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if ($request->getMethod() == 'POST') {
            $formData = $request->request->all();
            $this->getUserService()->initPassword($user['id'], $formData['newPassword']);

            return $this->redirect($this->generateUrl('homepage'));
        }

        return $this->render('init-password-change/index.html.twig');
    }

    protected function getUserService()
    {
        return $this->createService('CorporateTrainingBundle:User:UserService');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
