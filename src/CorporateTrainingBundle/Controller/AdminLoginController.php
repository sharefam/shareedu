<?php

namespace CorporateTrainingBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\ArrayToolkit;
use Biz\CloudPlatform\KeyApplier;
use AppBundle\Controller\LoginController as BaseLoginController;
use CorporateTrainingBundle\System;

class AdminLoginController extends BaseLoginController
{
    public function changePasswordAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if ($request->getMethod() == 'POST') {
            $formData = $request->request->all();
            $this->getUserService()->changePassword($user['id'], $formData['newPassword']);

            return $this->redirect($this->generateUrl('site_info_set'));
        }

        return $this->render('login/admin-login.html.twig');
    }

    public function setSiteInfoAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $provinces = $this->getAreaService()->findAreasByParentId(0);
        $provinces = ArrayToolkit::index($provinces, 'id');

        if ($request->getMethod() == 'POST') {
            $formData = $request->request->all();
            $formData['applicationVersion'] = System::CT_VERSION;
            $formData['domainName'] = $request->getHttpHost();
            $formData['status'] = false;
            $settings = $this->getCloudKey($user);

            if (!empty($settings['cloud_access_key'])) {
                $formData['accessKey'] = $settings['cloud_access_key'];
                $result = $this->postRequest('http://ct.edusoho.com/api/app_install', json_encode($formData));
                if (!empty($result) && isset($result['cloudStatus'])) {
                    $formData['status'] = true;
                }
            }

            $siteInfo = $this->getSettingService()->get('site', array());
            $siteInfo['name'] = $formData['companyName'];
            $this->getSettingService()->set('site', $siteInfo);
            $this->getSettingService()->set('site_info', $formData);
            $this->getUserService()->changePwdInit($user['id']);

            return $this->redirect($this->generateUrl('homepage'));
        }

        $settings = $this->getSettingService()->get('site', array());

        return $this->render('login/admin-complete-information.html.twig',
            array(
                'provinces' => $provinces,
                'setting' => $settings,
            ));
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

    protected function getCloudKey($user)
    {
        $applier = new KeyApplier();
        $keys = $applier->applyKey($user, 'training');

        $settings = $this->getSettingService()->get('storage', array());

        $settings['cloud_access_key'] = $keys['accessKey'];
        $settings['cloud_secret_key'] = $keys['secretKey'];
        $settings['cloud_key_applied'] = 1;

        $this->getSettingService()->set('storage', $settings);

        $settings = $this->getSettingService()->get('storage');

        return $settings;
    }

    protected function getAreaService()
    {
        return $this->createService('CorporateTrainingBundle:Area:AreaService');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
