<?php

namespace CorporateTrainingBundle\Biz\Announcement\Dao\Impl;

use Biz\Announcement\Dao\Impl\AnnouncementDaoImpl as BaseAnnouncementDaoImpl;

class AnnouncementDaoImpl extends BaseAnnouncementDaoImpl
{
    public function declares()
    {
        $declares = parent::declares();

        array_push($declares['conditions'], 'orgId IN (:orgIds)');

        return $declares;
    }

    public function initOrgsRelation($fields)
    {
        $sql = "UPDATE {$this->table} SET orgId = ?, orgCode = ?";

        return $this->db()->executeUpdate($sql, $fields);
    }
}
