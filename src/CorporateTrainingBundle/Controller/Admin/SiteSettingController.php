<?php

namespace CorporateTrainingBundle\Controller\Admin;

use AppBundle\Common\FileToolkit;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\Admin\SiteSettingController as BaseController;

class SiteSettingController extends BaseController
{
    public function logoAction(Request $request)
    {
        $site = $this->getSettingService()->get('site', array());
        $default = array(
            'logo' => '',
            'favicon' => '',
            'loginLogo' => '',
        );
        $site = array_merge($default, $site);

        return $this->render('admin/system/logo.html.twig', array(
            'site' => $site,
        ));
    }

    public function saveLogoAction(Request $request)
    {
        $logos = $request->request->all();
        $site = $this->getSettingService()->get('site', array());
        $site['logo'] = $logos['logo'];
        $site['loginLogo'] = $logos['loginLogo'];
        $site['favicon'] = $logos['favicon'];

        $this->getSettingService()->set('site', $site);
        $this->getLogService()->info('system', 'update_settings', '更新站点logo', $site);

        return $this->createJsonResponse(array(
            'message' => $this->trans('site.save.success'),
        ));
    }

    public function loginLogoUploadAction(Request $request)
    {
        $fileId = $request->request->get('id');
        $objectFile = $this->getFileService()->getFileObject($fileId);

        if (!FileToolkit::isImageFile($objectFile)) {
            throw $this->createAccessDeniedException('图片格式不正确！');
        }

        $file = $this->getFileService()->getFile($fileId);
        $parsed = $this->getFileService()->parseFileUri($file['uri']);

        $site = $this->getSettingService()->get('site', array());

        $oldFileId = empty($site['login_logo_file_id']) ? null : $site['login_logo_file_id'];
        $site['login_logo_file_id'] = $fileId;
        $site['loginLogo'] = "{$this->container->getParameter('topxia.upload.public_url_path')}/".$parsed['path'];
        $site['loginLogo'] = ltrim($site['loginLogo'], '/');

        $this->getSettingService()->set('site', $site);

        if ($oldFileId) {
            $this->getFileService()->deleteFile($oldFileId);
        }

        $this->getLogService()->info('system', 'update_settings', '更新站点登陆页LOGO', array('logo' => $site['loginLogo']));

        $response = array(
            'path' => $site['loginLogo'],
            'url' => $this->container->get('templating.helper.assets')->getUrl($site['loginLogo']),
        );

        return $this->createJsonResponse($response);
    }

    public function loginLogoRemoveAction(Request $request)
    {
        $setting = $this->getSettingService()->get('site');
        $setting['loginLogo'] = '';

        $fileId = empty($setting['login_logo_file_id']) ? null : $setting['login_logo_file_id'];
        $setting['login_logo_file_id'] = '';

        $this->getSettingService()->set('site', $setting);

        if ($fileId) {
            $this->getFileService()->deleteFile($fileId);
        }

        $this->getLogService()->info('system', 'update_settings', '移除站点登陆页LOGO');

        return $this->createJsonResponse(true);
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getAppService()
    {
        return $this->createService('CloudPlatform:AppService');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function getUserFieldService()
    {
        return $this->createService('User:UserFieldService');
    }

    protected function getAuthService()
    {
        return $this->createService('User:AuthService');
    }

    protected function getFileService()
    {
        return $this->createService('Content:FileService');
    }
}
