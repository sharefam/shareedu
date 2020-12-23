<?php

namespace CorporateTrainingBundle\Biz\UserGroup\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use CorporateTrainingBundle\Biz\UserGroup\Dao\MemberDao;

class MemberDaoImpl extends GeneralDaoImpl implements MemberDao
{
    protected $table = 'user_group_member';

    public function deleteByGroupId($groupId)
    {
        return $this->db()->delete($this->table(), array('groupId' => $groupId));
    }

    public function deleteByMemberIdAndMemberType($memberId, $memberType)
    {
        return $this->db()->delete($this->table(), array('memberId' => $memberId, 'memberType' => $memberType));
    }

    public function getByGroupIdAndMemberIdAndMemberType($groupId, $memberId, $memberType)
    {
        return $this->getByFields(
            array(
                'groupId' => $groupId,
                'memberId' => $memberId,
                'memberType' => $memberType,
            )
        );
    }

    public function findByGroupId($groupId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE groupId = ?";

        return $this->db()->fetchAll($sql, array($groupId));
    }

    public function findByGroupIdAndMemberType($groupId, $memberType)
    {
        $sql = "SELECT * FROM {$this->table} WHERE groupId = ? AND memberType = ?";

        return $this->db()->fetchAll($sql, array($groupId, $memberType));
    }

    public function findByMemberIdAndMemberType($memberId, $memberType)
    {
        $sql = "SELECT * FROM {$this->table} WHERE memberId = ? AND memberType = ?";

        return $this->db()->fetchAll($sql, array($memberId, $memberType));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'serializes' => array(),
            'orderbys' => array('id'),
            'conditions' => array(
                'groupId = :groupId',
                'memberId = :memberId',
                'memberType = :memberType',
            ),
        );
    }
}
