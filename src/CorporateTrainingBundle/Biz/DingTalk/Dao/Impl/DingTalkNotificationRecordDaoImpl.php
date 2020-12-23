<?php

namespace CorporateTrainingBundle\Biz\DingTalk\Dao\Impl;

use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;
use CorporateTrainingBundle\Biz\DingTalk\Dao\DingTalkNotificationRecordDao;

class DingTalkNotificationRecordDaoImpl extends AdvancedDaoImpl implements DingTalkNotificationRecordDao
{
    protected $table = 'dingtalk_notification_record';

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime'),
            'orderbys' => array(
                'createdTime',
                'id',
            ),
            'conditions' => array(
                'agentId = :agentId',
                'taskId = :taskId',
            ),
        );
    }
}
