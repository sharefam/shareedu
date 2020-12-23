<?php

namespace CorporateTrainingBundle\Biz\Teacher\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface LevelDao extends GeneralDaoInterface
{
    public function getByName($name);

    public function findByIds($ids);
}
