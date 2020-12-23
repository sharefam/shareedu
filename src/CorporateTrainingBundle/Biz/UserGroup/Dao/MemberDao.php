<?php

namespace CorporateTrainingBundle\Biz\UserGroup\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface MemberDao extends GeneralDaoInterface
{
    public function deleteByMemberIdAndMemberType($memberId, $memberType);

    public function deleteByGroupId($groupId);

    public function getByGroupIdAndMemberIdAndMemberType($groupId, $memberId, $memberType);

    public function findByGroupId($groupId);

    public function findByMemberIdAndMemberType($memberId, $memberType);
}
