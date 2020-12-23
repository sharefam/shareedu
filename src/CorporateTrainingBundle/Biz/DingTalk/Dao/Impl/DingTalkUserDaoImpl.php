<?php

namespace CorporateTrainingBundle\Biz\DingTalk\Dao\Impl;

use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;
use CorporateTrainingBundle\Biz\DingTalk\Dao\DingTalkUserDao;

class DingTalkUserDaoImpl extends AdvancedDaoImpl implements DingTalkUserDao
{
    protected $table = 'dingtalk_user';

    public function getByUnionid($unionid)
    {
        return $this->getByFields(array('unionid' => $unionid));
    }

    public function findByUnionids($unionids)
    {
        if (empty($unionids)) {
            return array();
        }
        $marks = str_repeat('?,', count($unionids) - 1).'?';
        $sql = "SELECT * FROM {$this->table} WHERE unionid IN ({$marks});";

        return $this->db()->fetchAll($sql, $unionids);
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'orderbys' => array(
                'createdTime',
                'id',
                'updatedTime',
            ),
            'conditions' => array(
                'unionid = :unionid',
                'unionid IN ( :unionids )',
                'userid IN ( :userids )',
                'userid = :userid',
            ),
        );
    }
}
