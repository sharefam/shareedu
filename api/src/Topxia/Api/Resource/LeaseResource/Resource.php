<?php

namespace Topxia\Api\Resource\LeaseResource;

use Silex\Application;
use Topxia\Api\Resource\BaseResource;
use Symfony\Component\HttpFoundation\Request;

class Resource extends BaseResource
{
    /**
     * @param Application $app
     * @param Request $request
     * @return array
     */
    public function post(Application $app, Request $request)
    {
        $token = $request->request->get('token', '');
        $resources = $request->request->get('resources', array());
        $resources = json_decode($resources, true);

        if (!$this->checkToken($token, $resources, $request->getScheme().'://'.$request->getHttpHost())) {
            return array('code' => '403', 'message' => 'Token invalid');
        }

        foreach ($resources as $key => $resource) {
            if (!in_array($resource['resourceType'], array('course'))) {
                continue;
            }

            $ucResourceType = ucfirst($resource['resourceType']);

            $duration += 60 * 2;
            $startJob = array(
                'name' => 'Lease'.$ucResourceType.'_'.$resource['resourceCode'],
                'expression' => intval(time() + $duration),
                'class' => 'CorporateTrainingBundle\Biz\LeaseResource\Job\Lease'.$ucResourceType.'Job',
                'misfire_threshold' => 60 * 10,
                'args' => array(
                    'resourceCode' => $resource['resourceCode'],
                ),
            );
            $this->getSchedulerService()->register($startJob);
        }

        return array('code' => 0, 'message' => '');
    }

    public function filter($res)
    {
        return $res;
    }

    private function checkToken($token, $params, $siteUrl)
    {
        $storage = $this->getSettingService()->get('storage', array());

        $text = $siteUrl."\n".json_encode($params)."\n".$storage['cloud_access_key'];
        $localToken = md5($text);

        if ($localToken == $token) {
            return true;
        }

       return false;
    }

    private function getSchedulerService()
    {
        return $this->getServiceKernel()->createService('Scheduler:SchedulerService');
    }
}
