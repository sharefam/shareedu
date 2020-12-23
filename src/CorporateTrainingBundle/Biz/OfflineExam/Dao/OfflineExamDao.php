<?php

namespace CorporateTrainingBundle\Biz\OfflineExam\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface OfflineExamDao extends GeneralDaoInterface
{
    public function findByIds($ids);

    public function getByIdAndTimeRange($id, $timeRange);
}
