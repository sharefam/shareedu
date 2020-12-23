<?php

namespace Biz\System\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface LogJoinUserDao extends GeneralDaoInterface
{
    public function searchLogJoinUser($conditions, $start, $limit);

    public function countLogJoinUser($conditions);
}
