<?php

namespace CorporateTrainingBundle\Biz\OfflineActivity\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Codeages\Biz\Framework\Event\Event;
use CorporateTrainingBundle\Biz\OfflineActivity\Dao\EnrollmentRecordDao;
use CorporateTrainingBundle\Biz\OfflineActivity\Service\EnrollmentRecordService;
use CorporateTrainingBundle\Biz\OfflineActivity\Service\MemberService;
use CorporateTrainingBundle\Biz\OfflineActivity\Service\OfflineActivityService;

class EnrollmentRecordServiceImpl extends BaseService implements EnrollmentRecordService
{
    public function createEnrollmentRecord($record)
    {
        if (!ArrayToolkit::requireds($record, array('userId', 'offlineActivityId'))) {
            throw $this->createInvalidArgumentException('parameter is invalid!');
        }

        $record = $this->filterFields($record);

        return $this->getEnrollmentRecordDao()->create($record);
    }

    public function updateEnrollmentRecord($id, $fields)
    {
        $fields = $this->filterFields($fields);

        return $this->getEnrollmentRecordDao()->update($id, $fields);
    }

    public function getEnrollmentRecord($id)
    {
        return $this->getEnrollmentRecordDao()->get($id);
    }

    public function countEnrollmentRecords(array $conditions)
    {
        return $this->getEnrollmentRecordDao()->count($conditions);
    }

    public function searchEnrollmentRecords(array $conditions, array $orderBys, $start, $limit)
    {
        return $this->getEnrollmentRecordDao()->search($conditions, $orderBys, $start, $limit);
    }

    public function getLatestEnrollmentRecordByActivityIdAndUserId($activityId, $userId)
    {
        return $this->getEnrollmentRecordDao()->getLatestByActivityIdAndUserId($activityId, $userId);
    }

    public function passOfflineActivityApply($recordId)
    {
        if (!$this->canAuditApply($recordId)) {
            throw $this->createServiceException('can not audit this apply');
        }

        $record = $this->getEnrollmentRecord($recordId);
        if ('submitted' != $record['status']) {
            throw $this->createServiceException('This apply cant not be approved');
        }

        $record = $this->updateEnrollmentRecord($recordId, array(
            'status' => 'approved',
            'approvedTime' => time(),
        ));

        $this->getMemberService()->becomeMember($record['offlineActivityId'], $record['userId']);

        return $record;
    }

    public function rejectOfflineActivityApply($recordId, $info = array())
    {
        if (!$this->canAuditApply($recordId)) {
            throw $this->createServiceException('can not audit this apply');
        }

        $record = $this->getEnrollmentRecord($recordId);
        if ('submitted' != $record['status']) {
            throw $this->createServiceException('This apply can not be rejected');
        }

        $fields['status'] = 'rejected';
        if (!empty($info['rejectedReason'])) {
            $fields['rejectedReason'] = $info['rejectedReason'];
        }

        $result = $this->updateEnrollmentRecord($recordId, $fields);
        $this->dispatchEvent('offline.reject.apply', new Event($result));

        return $result;
    }

    public function batchPass($recordIds)
    {
        try {
            $this->biz['db']->beginTransaction();

            foreach ($recordIds as $recordId) {
                $this->passOfflineActivityApply($recordId);
            }

            $this->dispatchEvent('offline_activity.batch.pass', $recordIds);

            $this->biz['db']->commit();
        } catch (\Exception $e) {
            $this->biz['db']->rollback();
            throw $e;
        }
    }

    public function batchReject($recordIds, $info = array())
    {
        try {
            $this->biz['db']->beginTransaction();

            foreach ($recordIds as $recordId) {
                $this->rejectOfflineActivityApply($recordId, $info);
            }

            $this->dispatchEvent(
                'offline_activity.batch.reject',
                array(
                    'recordIds' => $recordIds,
                    'rejectReason' => empty($info['rejectedReason']) ? '' : $info['rejectedReason'],
                )
            );
            $this->biz['db']->commit();
        } catch (\Exception $e) {
            $this->biz['db']->rollback();
            throw $e;
        }
    }

    public function calculateOfflineActivitySubmittedStudentNum($activityId)
    {
        return $this->getEnrollmentRecordDao()->calculateSubmittedStudentNumByActivityId($activityId);
    }

    public function findEnrollmentRecordsByActivityId($activityId)
    {
        return $this->getEnrollmentRecordDao()->findByActivityId($activityId);
    }

    public function findEnrollmentRecordsByIds($ids)
    {
        return $this->getEnrollmentRecordDao()->findByIds($ids);
    }

    public function deleteEnrollmentRecordByActivityIdAndUserId($activityId, $userId)
    {
        return $this->getEnrollmentRecordDao()->deleteByActivityIdAndUserId($activityId, $userId);
    }

    protected function canAuditApply($recordId)
    {
        if (!$this->getActivityService()->hasActivityManageRole()) {
            return false;
        }

        $record = $this->getEnrollmentRecord($recordId);
        if (empty($record)) {
            return false;
        }

        return true;
    }

    protected function filterFields($fields)
    {
        return ArrayToolkit::parts(
            $fields,
            array(
                'userId',
                'offlineActivityId',
                'submittedTime',
                'status',
                'approvedTime',
                'rejectedReason',
            )
        );
    }

    /**
     * @return OfflineActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineActivity:OfflineActivityService');
    }

    /**
     * @return MemberService
     */
    protected function getMemberService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineActivity:MemberService');
    }

    /**
     * @return EnrollmentRecordDao
     */
    protected function getEnrollmentRecordDao()
    {
        return $this->createDao('CorporateTrainingBundle:OfflineActivity:EnrollmentRecordDao');
    }
}
