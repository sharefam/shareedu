<?php

namespace CorporateTrainingBundle\Common;

use Biz\User\Service\Impl\UserSerialize;
use Biz\User\Service\UserService;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;

class TrialInitializer
{
    public function init()
    {
        $this->initSystemUserForGetData();
        $this->initTrialSetting();
    }

    protected function initSystemUserForGetData()
    {
        $user['type'] = 'system';
        $user['roles'] = array('ROLE_USER', 'ROLE_SUPER_ADMIN');
        $user['emailVerified'] = 1;
        $user['email'] = $this->generateEmail($user);
        $user['nickname'] = 'trial';
        $user['password'] = 'sitetrial';
        $user['uuid'] = $this->getUserService()->generateUUID();
        $user['salt'] = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $user['password'] = $this->getPasswordEncoder()->encodePassword($user['password'], $user['salt']);
        $user = UserSerialize::unserialize(
            $this->getUserDao()->create(UserSerialize::serialize($user))
        );

        $profile = array();
        $profile['id'] = $user['id'];
        $this->getUserProfileDao()->create($profile);
    }

    protected function generateEmail($registration, $maxLoop = 100)
    {
        for ($i = 0; $i < $maxLoop; ++$i) {
            $registration['email'] = 'user_'.substr($this->getRandomChar(), 0, 9).'@edusoho.net';

            if ($this->getUserService()->isEmailAvaliable($registration['email'])) {
                break;
            }
        }

        return $registration['email'];
    }

    protected function getRandomChar()
    {
        return base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
    }

    protected function initTrialSetting()
    {
        touch(ServiceKernel::instance()->getParameter('kernel.root_dir').'/data/trial.lock');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return ServiceKernel::instance()->getBiz()->service('User:UserService');
    }

    private function getUserDao()
    {
        return ServiceKernel::instance()->getBiz()->dao('User:UserDao');
    }

    private function getUserProfileDao()
    {
        return ServiceKernel::instance()->getBiz()->dao('User:UserProfileDao');
    }

    protected function getPasswordEncoder()
    {
        return new MessageDigestPasswordEncoder('sha256');
    }
}
