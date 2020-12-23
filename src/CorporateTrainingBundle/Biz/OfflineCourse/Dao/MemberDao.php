<?php

namespace CorporateTrainingBundle\Biz\OfflineCourse\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface MemberDao extends GeneralDaoInterface
{
    public function getByOfflineCourseIdAndUserId($offlineCourseId, $userId);

    public function findByIds($ids);

    public function findByOfflineCourseId($offlineCourseId);

    public function findByUserId($userId);
}
