<?php

namespace CorporateTrainingBundle\Biz\Announcement\Service\Impl;

use Biz\Announcement\Service\Impl\AnnouncementServiceImpl as BaseAnnouncementService;

class AnnouncementServiceImpl extends BaseAnnouncementService
{
    public function initOrgsRelation()
    {
        $fields = array('1', '1.');

        return $this->getAnnouncementDao()->initOrgsRelation($fields);
    }
}
