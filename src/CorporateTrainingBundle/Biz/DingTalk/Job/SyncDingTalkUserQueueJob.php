<?php

namespace CorporateTrainingBundle\Biz\DingTalk\Job;

use Biz\System\Service\LogService;
use Codeages\Biz\Framework\Queue\AbstractJob;
use Codeages\Biz\Framework\Queue\Service\QueueService;
use CorporateTrainingBundle\Biz\DingTalk\Client\DingTalkClientFactory;
use CorporateTrainingBundle\Biz\DingTalk\Service\DingTalkUserService;
use CorporateTrainingBundle\Biz\DingTalk\Service\Impl\DingTalkServiceImpl;
use CorporateTrainingBundle\Biz\User\Service\UserService;

class SyncDingTalkUserQueueJob extends AbstractJob
{
    public function execute()
    {
        $context = $this->getBody();
        $startNum = $context['startNum'];
        $perPageNum = 20;
        $counts = $this->getUserService()->countUserBinds(array('type' => 'dingtalk'));
        $users = $this->getUserService()->searchUserBinds(array('type' => 'dingtalk'), array(), $startNum, $perPageNum, array('fromId'));
        try {
            $dingtalkClient = DingTalkClientFactory::create();
            foreach ($users as $user) {
                $dingTalkUserInfo = $dingtalkClient->getUserInfoByUnionid($user['fromId']);
                if (!empty($dingTalkUserInfo['userid'])) {
                    $this->getDingTalkUserService()->createUser(array('unionid' => $user['fromId'], 'userid' => $dingTalkUserInfo['userid']));
                }
            }

            if ($startNum < $counts) {
                $dingTalkJob = new self(
                    array(
                        'startNum' => $startNum + $perPageNum,
                    )
                );
                $this->getQueueService()->pushJob($dingTalkJob, 'database');
            }
        } catch (\Exception $e) {
            $message = $e->getMessage().'startNum:'.$startNum;
            $this->getLogService()->error('dingtalk', 'dingtalk_notification', '钉钉用户同步失败:'.$message);
        }
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->biz->service('System:LogService');
    }

    /**
     * @return DingTalkServiceImpl
     */
    protected function getDingTalkService()
    {
        return $this->biz->service('CorporateTrainingBundle:DingTalk:DingTalkService');
    }

    /**
     * @return QueueService
     */
    protected function getQueueService()
    {
        return $this->biz->service('Queue:QueueService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->biz->service('CorporateTrainingBundle:User:UserService');
    }

    private function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }

    /**
     * @return DingTalkUserService
     */
    protected function getDingTalkUserService()
    {
        return $this->biz->service('CorporateTrainingBundle:DingTalk:DingTalkUserService');
    }
}
