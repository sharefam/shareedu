<?php

namespace CorporateTrainingBundle\Controller\Admin;

use AppBundle\Common\Paginator;
use AppBundle\Controller\Admin\BaseController;
use CorporateTrainingBundle\Biz\OfflineActivity\Service\OfflineActivityService;
use Symfony\Component\HttpFoundation\Request;

class OfflineActivityController extends BaseController
{
    public function listAction(Request $request)
    {
        $conditions = $request->request->all();
        $conditions['orgIds'] = $this->prepareOrgIds($conditions);

        $offlineActivitiesCount = $this->getOfflineActivityService()->countOfflineActivities($conditions);
        $paginator = new Paginator($this->get('request'), $offlineActivitiesCount, 10);
        $paginator->setBaseUrl($this->generateUrl('admin_offline_activity_ajax_list'));
        $offlineActivities = $this->getOfflineActivityService()->searchOfflineActivities(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('admin/offline-activity/index.html.twig', array(
            'offlineActivities' => $offlineActivities,
            'offlineActivitiesCount' => $offlineActivitiesCount,
            'paginator' => $paginator,
            'orgIds' => implode(',', $conditions['orgIds']),
        ));
    }

    public function ajaxListAction(Request $request)
    {
        $conditions = $request->request->all();
        $conditions['orgIds'] = $this->prepareOrgIds($conditions);

        $offlineActivitiesCount = $this->getOfflineActivityService()->countOfflineActivities($conditions);
        $paginator = new Paginator($this->get('request'), $offlineActivitiesCount, 10);
        $offlineActivities = $this->getOfflineActivityService()->searchOfflineActivities(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('admin/offline-activity/offline-activity-table.html.twig', array(
            'offlineActivities' => $offlineActivities,
            'offlineActivitiesCount' => $offlineActivitiesCount,
            'paginator' => $paginator,
            'orgIds' => implode(',', $conditions['orgIds']),
        ));
    }

    /**
     * @return OfflineActivityService
     */
    protected function getOfflineActivityService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineActivity:OfflineActivityService');
    }
}
