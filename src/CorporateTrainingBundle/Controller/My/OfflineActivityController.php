<?php

namespace CorporateTrainingBundle\Controller\My;

use AppBundle\Controller\BaseController;
use CorporateTrainingBundle\Biz\OfflineActivity\Service\OfflineActivityService;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;

class OfflineActivityController extends BaseController
{
    public function enrollmentRecordAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $conditions['userId'] = $user['id'];

        $count = $this->getOfflineActivityEnrollmentRecordService()->countEnrollmentRecords($conditions);
        $paginator = new Paginator(
            $request,
            $count,
            10
        );

        $enrollmentRecords = $this->getOfflineActivityEnrollmentRecordService()->searchEnrollmentRecords(
            $conditions,
            array('submittedTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $offlineActivityIds = ArrayToolkit::column($enrollmentRecords, 'offlineActivityId');
        $offlineActivities = $this->getOfflineActivityService()->findOfflineActivitiesByIds($offlineActivityIds);
        $offlineActivities = ArrayToolkit::index($offlineActivities, 'id');

        foreach ($enrollmentRecords as &$enrollmentRecord) {
            $enrollmentRecord['offlineActivity'] = $offlineActivities[$enrollmentRecord['offlineActivityId']];
        }

        return $this->render(
            'study-record/offline-activity/offline-activity.html.twig',
            array(
                'enrollmentRecords' => $enrollmentRecords,
                'type' => 'enrollment_record',
                'paginator' => $paginator,
                'userId' => $user['id'],
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

    protected function getOfflineActivityMemberService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineActivity:MemberService');
    }

    protected function getOfflineActivityEnrollmentRecordService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineActivity:EnrollmentRecordService');
    }
}
