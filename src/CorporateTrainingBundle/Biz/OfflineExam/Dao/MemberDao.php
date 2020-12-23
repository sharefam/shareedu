<?php

namespace CorporateTrainingBundle\Biz\OfflineExam\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface MemberDao extends GeneralDaoInterface
{
    public function findByIds($ids);

    public function findByOfflineExamId($offlineExamId);

    public function getByOfflineExamIdAndUserId($offlineExamId, $userId);
}
