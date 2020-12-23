<?php

namespace CorporateTrainingBundle\Biz\DingTalk\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use CorporateTrainingBundle\Biz\DingTalk\Dao\DingTalkUserDao;
use CorporateTrainingBundle\Biz\DingTalk\Service\DingTalkUserService;

class DingTalkUserServiceImpl extends BaseService implements DingTalkUserService
{
    public function createUser($user)
    {
        if (!ArrayToolkit::requireds($user, array('unionid', 'userid'))) {
            throw $this->createInvalidArgumentException('Lack of required fields');
        }
        $user = ArrayToolkit::parts($user, array('unionid', 'userid'));
        $existUser = $this->getUserByUnionid($user['unionid']);
        if (!empty($existUser)) {
            return $this->updateUser($existUser['id'], array('userid' => $user['userid']));
        }

        return $this->getDingTalkUserDao()->create($user);
    }

    public function updateUser($id, $fields)
    {
        $fields = ArrayToolkit::parts($fields, array('unionid', 'userid'));

        return $this->getDingTalkUserDao()->update($id, $fields);
    }

    public function getUserByUnionid($unionid)
    {
        return $this->getDingTalkUserDao()->getByUnionid($unionid);
    }

    public function findUsersByUnionids($unionids)
    {
        return $this->getDingTalkUserDao()->findByUnionids($unionids);
    }

    /**
     * @return DingTalkUserDao
     */
    protected function getDingTalkUserDao()
    {
        return $this->createDao('CorporateTrainingBundle:DingTalk:DingTalkUserDao');
    }
}
