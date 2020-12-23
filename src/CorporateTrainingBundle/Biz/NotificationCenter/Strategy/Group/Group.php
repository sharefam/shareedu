<?php

namespace CorporateTrainingBundle\Biz\NotificationCenter\Strategy\Group;

interface Group
{
    public function findGroupEmails($params);

    public function findGroupMobiles($params);

    public function findGroupDingTalkUsers($params);
}
