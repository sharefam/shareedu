<?php

namespace CorporateTrainingBundle\Biz\OrgSync\Service;

interface OrgSyncService
{
    public function syncFrom();

    public function syncTo();

    public function createOrgSync($org);

    public function updateOrgSync($org);

    public function deleteOrgSync($id);
}
