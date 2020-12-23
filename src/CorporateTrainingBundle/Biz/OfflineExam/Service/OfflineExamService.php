<?php

namespace CorporateTrainingBundle\Biz\OfflineExam\Service;

interface OfflineExamService
{
    public function createOfflineExam($offlineExam);

    public function updateOfflineExam($id, $fields);

    public function deleteOfflineExam($id);

    public function getOfflineExam($id);

    public function getOfflineExamByIdAndTimeRange($id, $timeRange);

    public function findOfflineExamByIds($ids);

    public function searchOfflineExams($conditions, $orderBys, $start, $limit);

    public function countOfflineExams($conditions);

    public function publishOfflineExam($id);

    public function closeOfflineExam($id);
}
