<?php

namespace CorporateTrainingBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use AppBundle\Component\OAuthClient\OAuthClientFactory;
use AppBundle\Controller\LoginController as BaseLoginController;
use CorporateTrainingBundle\System;

class LoginController extends BaseLoginController
{
    public function indexAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if ($user->isLogin()) {
            return $this->createMessageResponse('info', 'login.message.repeat_login', null, 3000, $this->generateUrl('homepage'));
        }

        if ($request->attributes->has(Security::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(Security::AUTHENTICATION_ERROR);
        } else {
            $error = $request->getSession()->get(Security::AUTHENTICATION_ERROR);
        }

        if ($this->getWebExtension()->isMicroMessenger() && $this->setting('login_bind.enabled', 0) && $this->setting('login_bind.weixinmob_enabled', 0)) {
            $inviteCode = $request->query->get('inviteCode', '');

            return $this->redirect($this->generateUrl('login_bind', array('type' => 'weixinmob', '_target_path' => $this->getTargetPath($request), 'inviteCode' => $inviteCode)));
        }

        if ($this->getCTWebExtension()->isDingTalk() && $this->setting('login_bind.enabled', 0) && $this->setting('login_bind.dingtalkmob_enabled', 0)) {
            $inviteCode = $request->query->get('inviteCode', '');

            return $this->redirect($this->generateUrl('login_bind', array('type' => 'dingtalkmob', '_target_path' => $this->getTargetPath($request), 'inviteCode' => $inviteCode)));
        }

        $this->callRemoteService($request, $user);

        return $this->render(
            'login/index.html.twig',
            array(
            'last_username' => $request->getSession()->get(Security::LAST_USERNAME),
            'error' => $error,
            '_target_path' => $this->getTargetPath($request),
            )
        );
    }

    public function ajaxAction(Request $request)
    {
        $clients = OAuthClientFactory::clients();

        return $this->render('login/ajax.html.twig', array(
            '_target_path' => $this->getTargetPath($request),
            'clients' => $clients,
        ));
    }

    protected function callRemoteService(Request $request, $user)
    {
        if (in_array('ROLE_SUPER_ADMIN', $user['roles'])) {
            $siteInfo = $this->getSettingService()->get('site_info', array());
            if (!empty($siteInfo) && $siteInfo['status'] == false) {
                $siteInfo['applicationVersion'] = System::CT_VERSION;
                $siteInfo['domainName'] = $request->getHttpHost();
                if (empty($siteInfo['cloud_key'])) {
                    $settings = $this->getSettingService()->get('storage', array());
                    if (!empty($settings['cloud_access_key'])) {
                        $siteInfo['accessKey'] = $settings['cloud_access_key'];
                        $this->postRequest('http://ct.edusoho.com/api/app_install', json_encode($siteInfo));
                    }
                }
            }
        }
    }

    protected function postRequest($url, $params)
    {
        $curl = curl_init();

        $headers = array();
        $headers[] = 'Content-type: application/json';
        $headers[] = 'Accept: application/vnd.edusoho.v2+json';

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Corporate Training App');
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        curl_setopt($curl, CURLOPT_URL, $url);

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }

    protected function getCTWebExtension()
    {
        return $this->container->get('corporatetrainingbundle.twig.web_extension');
    }

    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }
}
