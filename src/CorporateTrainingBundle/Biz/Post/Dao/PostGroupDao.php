<?php

namespace CorporateTrainingBundle\Biz\Post\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface PostGroupDao extends GeneralDaoInterface
{
    public function getByName($name);

    public function findByIds($ids);
}
