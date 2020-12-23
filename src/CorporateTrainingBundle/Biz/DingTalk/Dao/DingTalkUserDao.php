<?php

namespace CorporateTrainingBundle\Biz\DingTalk\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface DingTalkUserDao extends AdvancedDaoInterface
{
    public function getByUnionid($unionid);

    public function findByUnionids($unionids);
}
