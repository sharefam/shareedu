<?php

namespace Biz\File\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface FileUsedDao extends GeneralDaoInterface
{
    public function getByFileId($fileId);
}
