<?php

namespace CorporateTrainingBundle\Biz\Post\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface PostDao extends GeneralDaoInterface
{
    public function getByName($name);

    public function getByCode($code);

    public function getByGroupIdAndRankId($groupId, $rankId);

    public function findAll();

    public function findByGroupId($groupId);

    public function findByIds($ids);

    public function findBySeqAndGroupId($seq, $groupId);
}
