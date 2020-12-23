<?php

namespace CorporateTrainingBundle\Biz\User\Event;

use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use CorporateTrainingBundle\Component\EIMClient\UserFactory;
use CorporateTrainingBundle\Component\EIMClient\DepartmentFactory;
use AppBundle\Common\ArrayToolkit;

class UserEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'user.bind' => 'onUserBind',
        );
    }

    public function onUserBind(Event $event)
    {
        $bind = $event->getSubject();
        $setting = $this->getSettingService()->get('sync_department_setting', array());

        if ($bind['type'] == 'dingtalk' && $setting['enable']) {
            $client = UserFactory::create($setting);
            $userId = $client->getUserIdByUnionId($bind['fromId']);
            $user = $client->get($userId);

            if ($user['errcode'] != 0) {
                return;
            }

            $departments = array();
            $departmentClient = DepartmentFactory::create($setting);

            foreach ($user['department'] as $departmentId) {
                array_push($departments, $departmentClient->get($departmentId));
            }
        }

        if (!empty($departments)) {
            $departmentIds = ArrayToolkit::column($departments, 'id');
            $orgs = $this->getOrgService()->findOrgsBySyncIds($departmentIds);

            $this->getUserDao()->update($bind['toId'], array(
                'orgIds' => ArrayToolkit::column($orgs, 'id'),
                'orgCodes' => ArrayToolkit::column($orgs, 'orgCode'),
                )
            );
            if (!empty($orgs)) {
                $this->getUserOrgService()->setUserOrgs($bind['toId'], $orgs);
            }
        }
    }

    protected function getUserDao()
    {
        return $this->getBiz()->dao('User:UserDao');
    }

    protected function getOrgService()
    {
        return $this->getBiz()->service('Org:OrgService');
    }

    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\User\Service\UserOrgService
     */
    protected function getUserOrgService()
    {
        return $this->getBiz()->service('CorporateTrainingBundle:User:UserOrgService');
    }
}
