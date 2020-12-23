<?php

namespace CorporateTrainingBundle\Controller\Admin;

use AppBundle\Controller\Admin\EduCloudController as BaseController;
use Biz\CloudPlatform\KeyApplier;
use Biz\CloudPlatform\CloudAPIFactory;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;

class EduCloudController extends BaseController
{
    public function keyApplyAction(Request $request)
    {
        $applier = new KeyApplier();
        $keys = $applier->applyKey($this->getUser(), 'training');

        if (empty($keys['accessKey']) || empty($keys['secretKey'])) {
            return $this->createJsonResponse(array('error' => ServiceKernel::instance()->trans('admin.edu_cloud_key.message.key_apply_error')));
        }

        $settings = $this->getSettingService()->get('storage', array());

        $settings['cloud_access_key'] = $keys['accessKey'];
        $settings['cloud_secret_key'] = $keys['secretKey'];
        $settings['cloud_key_applied'] = 1;

        $this->getSettingService()->set('storage', $settings);

        return $this->createJsonResponse(array('status' => 'ok'));
    }

    public function attachmentAction(Request $request)
    {
        $attachment = $this->getSettingService()->get('cloud_attachment', array());
        $defaultData = array('article' => 0, 'course' => 0, 'classroom' => 0, 'group' => 0, 'question' => 0, 'qa' => 0, 'projectPlaning' => 1);
        $default = array_merge($defaultData, array('enable' => 1, 'fileSize' => 500));
        $attachment = array_merge($default, $attachment);

        if ($request->getMethod() == 'POST') {
            $attachment = $request->request->all();
            $attachment = array_merge($default, $attachment);
            $this->getSettingService()->set('cloud_attachment', $attachment);
            $this->setFlashMessage('success', 'site.save.success');
        }
        //云端视频判断
        try {
            $api = CloudAPIFactory::create('root');
            $info = $api->get('/me');
        } catch (\RuntimeException $e) {
            return $this->render('admin/edu-cloud/video-error.html.twig', array());
        }

        return $this->render('admin/edu-cloud/cloud-attachment.html.twig', array(
            'attachment' => $attachment,
            'info' => $info,
        ));
    }
}
