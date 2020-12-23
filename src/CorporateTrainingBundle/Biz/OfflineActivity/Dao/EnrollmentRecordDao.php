<?php

namespace CorporateTrainingBundle\Biz\OfflineActivity\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface EnrollmentRecordDao extends GeneralDaoInterface
{
    public function getLatestByActivityIdAndUserId($activityId, $userId);

    public function calculateSubmittedStudentNumByActivityId($activityId);

    public function findByActivityId($activityId);

    public function findByIds($ids);

    public function deleteByActivityIdAndUserId($activityId, $userId);
}
