<?php

namespace CorporateTrainingBundle\Biz\User\Dao\Impl;

use Biz\User\Dao\Impl\UserDaoImpl as BaseDaoImpl;
use Codeages\Biz\Framework\Dao\DaoException;
use Codeages\Biz\Framework\Dao\DynamicQueryBuilder;

class UserDaoImpl extends BaseDaoImpl
{
    public function searchUsers($conditions, $orderBys, $start, $limit, $columns = array())
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->createUserQueryBuilder($conditions)
            ->setFirstResult($start)
            ->setMaxResults($limit);

        $this->addSelect($builder, $columns);

        $declares = $this->declares();
        foreach ($orderBys ?: array() as $field => $direction) {
            if (!in_array($field, $declares['orderbys'])) {
                throw new DaoException(
                    sprintf(
                        "SQL order by field is only allowed '%s', but you give `{$field}`.",
                        implode(',', $declares['orderbys'])
                    )
                );
            }
            if (!in_array(strtoupper($direction), array('ASC', 'DESC'))) {
                throw new DaoException(
                    "SQL order by direction is only allowed `ASC`, `DESC`, but you give `{$direction}`."
                );
            }
            $builder->addOrderBy($field, $direction);
        }

        return $builder->execute()->fetchAll() ?: array();
    }

    private function addSelect(DynamicQueryBuilder $builder, $columns)
    {
        if (!$columns) {
            return $builder->select('user.*');
        }

        foreach ($columns as $column) {
            if ('truename' == $column) {
                $builder->addSelect('profile.truename');
            } elseif (preg_match('/^\w+$/', $column)) {
                $builder->addSelect('user.'.$column);
            } else {
                throw new DaoException('Illegal column name: '.$column);
            }
        }

        return $builder;
    }

    public function countUsers($conditions)
    {
        $builder = $this->createUserQueryBuilder($conditions)
            ->select('COUNT(user.id)');

        return $builder->execute()->fetchColumn(0);
    }

    public function statisticsOrgUserNumGroupByOrgId()
    {
        $sql = 'SELECT o.orgId, COUNT(u.id) AS count FROM `user_org` o LEFT JOIN `user` u ON o.userId = u.id AND u.locked = 0 GROUP BY o.orgId';

        return $this->db()->fetchAll($sql);
    }

    public function statisticsPostUserNumGroupByPostId()
    {
        $sql = "SELECT postId, COUNT(id) AS count FROM {$this->table} WHERE locked = ? GROUP BY postId";

        return $this->db()->fetchAll($sql, array(0));
    }

    public function initOrgsRelation($fields)
    {
        $sql = "UPDATE {$this->table} SET orgIds = ?, orgCodes = ?";

        return $this->db()->executeUpdate($sql, $fields);
    }

    public function findUserIds(array $conditions)
    {
        $builder = $this->createUserQueryBuilder($conditions)
            ->select('DISTINCT user.id');

        return $builder->execute()->fetchAll() ?: array();
    }

    protected function createUserQueryBuilder($conditions)
    {
        $conditions = array_filter(
            $conditions,
            function ($v) {
                if (0 === $v) {
                    return true;
                }

                if (empty($v)) {
                    return false;
                }

                return true;
            }
        );

        if (isset($conditions['roles'])) {
            $conditions['roles'] = "%{$conditions['roles']}%";
        }

        if (isset($conditions['role'])) {
            $conditions['role'] = "|{$conditions['role']}|";
        }

        if (isset($conditions['keywordType']) && isset($conditions['keyword'])) {
            if ('loginIp' == $conditions['keywordType']) {
                $conditions[$conditions['keywordType']] = "{$conditions['keyword']}";
            } else {
                $conditions[$conditions['keywordType']] = "%{$conditions['keyword']}%";
            }

            unset($conditions['keywordType']);
            unset($conditions['keyword']);
        }

        if (isset($conditions['keywordUserType'])) {
            $conditions['type'] = "%{$conditions['keywordUserType']}%";
            unset($conditions['keywordUserType']);
        }

        if (isset($conditions['nickname'])) {
            $conditions['nickname'] = "%{$conditions['nickname']}%";
        }

        if (!empty($conditions['datePicker']) && 'longinDate' == $conditions['datePicker']) {
            if (isset($conditions['startDate'])) {
                $conditions['loginStartTime'] = strtotime($conditions['startDate']);
            }

            if (isset($conditions['endDate'])) {
                $conditions['loginEndTime'] = strtotime($conditions['endDate']);
            }
        }

        if (!empty($conditions['datePicker']) && 'registerDate' == $conditions['datePicker']) {
            if (isset($conditions['startDate'])) {
                $conditions['startTime'] = strtotime($conditions['startDate']);
            }

            if (isset($conditions['endDate'])) {
                $conditions['endTime'] = strtotime($conditions['endDate']);
            }
        }

        if (isset($conditions['likeOrgCode'])) {
            $conditions['likeOrgCode'] = '%|'.$conditions['likeOrgCode'].'%';
            unset($conditions['orgCode']);
        }

        if (isset($conditions['truename'])) {
            $conditions['truename'] = "%{$conditions['truename']}%";
        }

        $conditions['verifiedMobileNull'] = '';

        $builder = new DynamicQueryBuilder($this->db(), $conditions);
        $builder->from($this->table, 'user')
            ->join('user', 'user_profile', 'profile', 'user.id = profile.id')
            ->andWhere('user.promoted = :promoted')
            ->andWhere('user.roles LIKE :roles')
            ->andWhere('user.roles = :role')
            ->andWhere('UPPER(user.nickname) LIKE :nickname')
            ->andWhere('user.id =: id')
            ->andWhere('user.loginIp = :loginIp')
            ->andWhere('user.createdIp = :createdIp')
            ->andWhere('user.approvalStatus = :approvalStatus')
            ->andWhere('UPPER(user.email) LIKE :email')
            ->andWhere('user.level = :level')
            ->andWhere('user.createdTime >= :startTime')
            ->andWhere('user.createdTime <= :endTime')
            ->andWhere('user.updatedTime >= :updatedTime_GE')
            ->andWhere('user.approvalTime >= :startApprovalTime')
            ->andWhere('user.approvalTime <= :endApprovalTime')
            ->andWhere('user.loginTime >= :loginStartTime')
            ->andWhere('user.loginTime <= :loginEndTime')
            ->andWhere('user.locked = :locked')
            ->andWhere('user.level >= :greatLevel')
            ->andWhere('UPPER(user.verifiedMobile) LIKE :verifiedMobile')
            ->andWhere('user.type LIKE :type')
            ->andWhere('user.id IN ( :userIds)')
            ->andWhere('user.inviteCode = :inviteCode')
            ->andWhere('user.inviteCode != :NoInviteCode')
            ->andWhere('user.id NOT IN ( :excludeIds )')
            ->andWhere('user.postId = :postId')
            ->andWhere('user.postId IN ( :postIds)')
            ->andWhere('profile.truename LIKE :truename')
            ->andWhere('user.type != :noType')
            ->andWhere('user.orgCodes LIKE :likeOrgCode')
            ->andWhere('user.hireDate >= :hireDate_GTE')
            ->andWhere('user.hireDate <= :hireDate_LTE');

        if (array_key_exists('hasVerifiedMobile', $conditions)) {
            $builder = $builder->andWhere('user.verifiedMobile != :verifiedMobileNull');
        }

        return $builder;
    }

    public function declares()
    {
        return array(
            'serializes' => array(
                'roles' => 'delimiter',
                'orgIds' => 'delimiter',
                'orgCodes' => 'delimiter',
            ),
            'orderbys' => array(
                'id',
                'createdTime',
                'updatedTime',
                'promotedTime',
                'promoted',
                'promotedSeq',
                'nickname',
                'loginTime',
            ),
            'timestamps' => array(
                'createdTime',
                'updatedTime',
            ),
            'conditions' => array(
                'mobile = :mobile',
                'promoted = :promoted',
                'roles LIKE :roles',
                'roles = :role',
                'UPPER(nickname) LIKE :nickname',
                'id =: id',
                'loginIp = :loginIp',
                'createdIp = :createdIp',
                'approvalStatus = :approvalStatus',
                'UPPER(email) LIKE :email',
                'level = :level',
                'createdTime >= :startTime',
                'createdTime <= :endTime',
                'updatedTime >= :updatedTime_GE',
                'approvalTime >= :startApprovalTime',
                'approvalTime <= :endApprovalTime',
                'loginTime >= :loginStartTime',
                'loginTime <= :loginEndTime',
                'locked = :locked',
                'level >= :greatLevel',
                'UPPER(verifiedMobile) LIKE :verifiedMobile',
                'type LIKE :type',
                'id IN ( :userIds)',
                'inviteCode = :inviteCode',
                'inviteCode != :NoInviteCode',
                'id NOT IN ( :excludeIds )',
                'postId  = :postId',
                'type != :noType',
                'postId IN ( :postIds)',
                'orgCodes = :orgCodes',
                'pwdInit = :pwdInit',
                'user.hireDate >= :hireDate_GTE',
                'user.hireDate <= :hireDate_LTE',
            ),
        );
    }

    protected function filterStartLimit(&$start, &$limit)
    {
        $start = (int) $start;
        $limit = (int) $limit;
    }
}
