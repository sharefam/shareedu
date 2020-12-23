<?php

namespace CorporateTrainingBundle\Controller;

use AppBundle\Controller\SettingsController as BaseController;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Component\OAuthClient\OAuthClientFactory;

class SettingsController extends BaseController
{
    public function bindsAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $clients = OAuthClientFactory::clients();
        $userBinds = $this->getUserService()->findBindsByUserId($user->id) ?: array();

        foreach ($userBinds as $userBind) {
            if ($userBind['type'] === 'weixin') {
                $userBind['type'] = 'weixinweb';
            }

            if ($userBind['type'] == 'dingtalk') {
                $userBind['type'] = 'dingtalkweb';
            }

            $clients[$userBind['type']]['status'] = 'bind';
        }

        return $this->render('settings/binds.html.twig', array(
            'clients' => $clients,
        ));
    }
}
