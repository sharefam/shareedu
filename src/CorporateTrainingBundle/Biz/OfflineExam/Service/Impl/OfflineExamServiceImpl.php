<?php

namespace CorporateTrainingBundle\Biz\OfflineExam\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use CorporateTrainingBundle\Biz\OfflineExam\Service\OfflineExamService;

class OfflineExamServiceImpl extends BaseService implements OfflineExamService
{
    public function createOfflineExam($offlineExam)
    {
        $this->validateFields($offlineExam);
        $offlineExam = $this->filterFields($offlineExam);

        return $this->getOfflineExamDao()->create($offlineExam);
    }

    public function updateOfflineExam($id, $fields)
    {
        $fields = $this->filterFields($fields);

        return $this->getOfflineExamDao()->update($id, $fields);
    }

    public function deleteOfflineExam($id)
    {
        return $this->getOfflineExamDao()->delete($id);
    }

    public function getOfflineExam($id)
    {
        return $this->getOfflineExamDao()->get($id);
    }

    public function getOfflineExamByIdAndTimeRange($id, $timeRange)
    {
        return $this->getOfflineExamDao()->getByIdAndTimeRange($id, $timeRange);
    }

    public function findOfflineExamByIds($ids)
    {
        return $this->getOfflineExamDao()->findByIds($ids);
    }

    public function searchOfflineExams($conditions, $orderBys, $start, $limit)
    {
        return $this->getOfflineExamDao()->search($conditions, $orderBys, $start, $limit);
    }

    public function countOfflineExams($conditions)
    {
        return $this->getOfflineExamDao()->count($conditions);
    }

    public function publishOfflineExam($id)
    {
        return $this->updateOfflineExam($id, array('status' => 'published'));
    }

    public function closeOfflineExam($id)
    {
        return $this->updateOfflineExam($id, array('status' => 'closed'));
    }

    protected function validateFields($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('title'), true)) {
            throw $this->createInvalidArgumentException('Lack of required fields');
        }
    }

    protected function filterFields($fields)
    {
        return ArrayToolkit::parts(
            $fields,
            array(
                'title',
                'summary',
                'projectPlanId',
                'status',
                'score',
                'passScore',
                'creator',
                'startTime',
                'endTime',
                'place',
            )
        );
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineExam\Dao\Impl\OfflineExamDaoImpl
     */
    protected function getOfflineExamDao()
    {
        return $this->createDao('CorporateTrainingBundle:OfflineExam:OfflineExamDao');
    }
}
