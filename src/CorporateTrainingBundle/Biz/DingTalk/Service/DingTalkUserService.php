<?php

namespace CorporateTrainingBundle\Biz\DingTalk\Service;

interface DingTalkUserService
{
    public function createUser($user);

    public function updateUser($id, $fields);

    public function getUserByUnionid($unionid);

    public function findUsersByUnionids($unionids);
}
