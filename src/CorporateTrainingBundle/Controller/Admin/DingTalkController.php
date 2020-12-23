<?php

namespace CorporateTrainingBundle\Controller\Admin;

use AppBundle\Controller\Admin\BaseController;
use Symfony\Component\HttpFoundation\Request;

class DingTalkController extends BaseController
{
    protected function getBaseUrl()
    {
        if (isset($_SERVER['HTTPS']) && 'on' === $_SERVER['HTTPS']) {
            return 'https://'.$_SERVER['HTTP_HOST'];
        }

        return 'http://'.$_SERVER['HTTP_HOST'];
    }

    public function indexAction(Request $request)
    {
        if ('POST' === $request->getMethod()) {
            $dingtalkInformation = $request->request->all();
            $this->getSettingService()->set('dingtalk_notification', $dingtalkInformation);
            $this->setFlashMessage('success', 'site.save.success');
        }

        return $this->render(
            'CorporateTrainingBundle::admin/dingtalk/set.html.twig',
            array(
            )
        );
    }

    protected function parseTemplate($templateType, $params)
    {
        $biz = $this->getBiz();

        return $biz['dingtalk_template_parser']->parseTemplate($templateType, $params);
    }

    public function previewTemplateAction(Request $request, $type)
    {
        return $this->render(
            'CorporateTrainingBundle::admin/dingtalk/preview-modal.html.twig',
            array('type' => $type)
        );
    }

    /**
     * @return SettingService
     */
    private function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
