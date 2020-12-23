<?php

namespace CorporateTrainingBundle\Biz\Area\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface AreaDao extends GeneralDaoInterface
{
    public function findByParentId($parentId);
}
