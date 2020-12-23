<?php

namespace CorporateTrainingBundle\Biz\DingTalk\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Exception\InvalidArgumentException;
use Biz\BaseService;
use Biz\System\Service\LogService;
use Biz\System\Service\SettingService;
use CorporateTrainingBundle\Biz\DingTalk\Client\DingTalkClientFactory;
use CorporateTrainingBundle\Biz\DingTalk\Service\DingTalkNotificationRecordService;
use CorporateTrainingBundle\Biz\DingTalk\Service\DingTalkService;
use CorporateTrainingBundle\Biz\DingTalk\Service\DingTalkUserService;
use CorporateTrainingBundle\Biz\User\Service\UserService;

class DingTalkServiceImpl extends BaseService implements DingTalkService
{
    const MAX_USER_NUM = 100;  //钉钉消息单次最大用户数（钉钉接口决定的）

    /**
     * @param array $template 钉钉场景模版
     * @param array $userIds  用户id
     *
     * @return bool
     *
     * @throws \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function sendDingTalkNotification($template, $userIds)
    {
        if (empty($userIds)) {
            return  true;
        }
        $this->checkNotificationData($userIds, $template);
        $userids = $this->getDingTalkUserids($userIds);
        if (empty($userids)) {
            return true;
        }
        $dingtalkClient = DingTalkClientFactory::create();
        $result = $dingtalkClient->sendMessage($userids, $template['message']);
        $this->createSendRecord($result, $template, $userids, $userIds);

        return true;
    }

    /**
     * @param array $userIds 用户id
     *
     * @return array 钉钉定义的用户字段userid
     */
    protected function getDingTalkUserids($userIds)
    {
        $users = $this->getUserService()->getDingTalkUsers($userIds);
        $unionids = ArrayToolkit::column($users, 'fromId');

        $dingTalkUsers = $this->getDingTalkUserService()->findUsersByUnionids($unionids);

        return empty($dingTalkUsers) ? array() : ArrayToolkit::column($dingTalkUsers, 'userid');
    }

    /**
     * @param array $result          钉钉返回的结果
     * @param array $template        钉钉发送模板
     * @param array $dingtalkUserids 钉钉定义的用户字段userid
     * @param array $userIds         用户id
     */
    protected function createSendRecord($result, $template, $dingtalkUserids, $userIds)
    {
        if (!isset($result['task_id'])) {
            return;
        }
        $syncSetting = $this->getSettingService()->get('sync_department_setting', array());
        $data = array(
            'dingtalkUserids' => $dingtalkUserids,
            'userIds' => $userIds,
            'mes' => $template['message'],
        );
        $template['taskId'] = $result['task_id'];
        $template['data'] = json_encode($data);
        $template['agentId'] = $syncSetting['agentId'];

        $this->getDingTalkNotificationRecordService()->createRecord($template);
    }

    protected function checkNotificationData($userIds, $template)
    {
        if (count($userIds) > self::MAX_USER_NUM) {
            throw new InvalidArgumentException('超出接口限制');
        }

        if (!ArrayToolkit::requireds($template, array('message', 'targetType', 'targetId', 'batch'))) {
            throw $this->createInvalidArgumentException('Lack of required fields');
        }
    }

    /**
     * @return SettingService
     */
    private function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return DingTalkUserService
     */
    protected function getDingTalkUserService()
    {
        return $this->createService('CorporateTrainingBundle:DingTalk:DingTalkUserService');
    }

    /**
     * @return DingTalkNotificationRecordService
     */
    protected function getDingTalkNotificationRecordService()
    {
        return $this->createService('CorporateTrainingBundle:DingTalk:DingTalkNotificationRecordService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('CorporateTrainingBundle:User:UserService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }
}
