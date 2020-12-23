<?php

namespace CorporateTrainingBundle\Biz\TeacherProfessionField\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface TeacherProfessionFieldDao extends GeneralDaoInterface
{
    public function getByName($name);

    public function findByIds($ids);
}
