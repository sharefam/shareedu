<?php

namespace CorporateTrainingBundle\Controller\Admin;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Controller\Admin\AppController as BaseController;
use Symfony\Component\HttpFoundation\Request;

class AppController extends BaseController
{
    public function installedAction(Request $request, $postStatus)
    {
        $apps = $this->getAppService()->getCenterApps() ?: array();

        $apps = ArrayToolkit::index($apps, 'code');

        $appsInstalled = $this->getAppService()->findApps(0, 100);
        $appsInstalled = ArrayToolkit::index($appsInstalled, 'code');

        $dir = dirname(dirname(dirname(dirname(__DIR__))));
        $appMeta = array();

        foreach ($apps as $key => $value) {
            unset($apps[$key]);

            $appInfo = $value;
            $code = strtolower($key);

            $apps[$code] = $appInfo;
        }

        foreach ($appsInstalled as $key => $value) {
            $appItem = $key;
            unset($appsInstalled[$key]);

            $appInfo = $value;
            $key = strtolower($key);

            $appsInstalled[$key] = $appInfo;
            $appsInstalled[$key]['installed'] = 1;
            $appsInstalled[$key]['icon'] = !empty($apps[$key]['icon']) ? $apps[$key]['icon'] : null;
            $appsInstalled[$key]['id'] = isset($apps[$key]) ? $apps[$key]['id'] : $appsInstalled[$key]['id'];

            if ($key != 'TRAININGMAIN') {
                if (in_array($key, array('vip', 'coupon'))) {
                    $key = ucfirst($appItem);
                } else {
                    $key = $appItem;
                }

                $dic = $dir.'/plugins/'.$key.'Plugin'.'/plugin.json';

                if (file_exists($dic)) {
                    $appMeta[$appItem] = json_decode(file_get_contents($dic));
                }
            }
        }

        $apps = array_merge($apps, $appsInstalled);

        if (array_key_exists('jianmo', $apps)) {
            unset($apps['jianmo']);
        }

        $theme = array();
        $plugin = array();

        foreach ($apps as $key => $value) {
            if ($value['type'] == 'theme') {
                $theme[] = $value;
            } elseif ($value['type'] == 'plugin' || $value['type'] == 'app') {
                $plugin[] = $value;
            }
        }

        return $this->render('admin/app/installed.html.twig', array(
            'apps' => $apps,
            'theme' => $theme,
            'plugin' => $plugin,
            'type' => $postStatus,
            'appMeta' => $appMeta,
        ));
    }
}
