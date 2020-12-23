<?php

namespace CorporateTrainingBundle\Controller\Admin\Train;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\BaseController;
use CorporateTrainingBundle\Biz\OfflineActivity\Service\OfflineActivityService;
use Symfony\Component\HttpFoundation\Request;

class OfflineActivityController extends BaseController
{
    public function manageAction(Request $request)
    {
        if (!$this->getOfflineActivityService()->hasActivityManageRole()) {
            return $this->createMessageResponse('error', 'offline_activity.manage.message.permission_error');
        }
        $conditions = $request->query->all();

        $offlineActivitiesCount = $this->getOfflineActivityService()->countOfflineActivities($conditions);
        $paginator = new Paginator($this->get('request'), $offlineActivitiesCount, 10);
        $offlineActivities = $this->getOfflineActivityService()->searchOfflineActivities(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render(
            'CorporateTrainingBundle::admin/train/offline-activity-list.html.twig',
            array(
                'paginator' => $paginator,
                'offlineActivities' => $offlineActivities,
            )
        );
    }

    /**
     * @return OfflineActivityService
     */
    protected function getOfflineActivityService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineActivity:OfflineActivityService');
    }
}
