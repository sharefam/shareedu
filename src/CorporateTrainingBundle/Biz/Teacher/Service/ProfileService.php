<?php

namespace CorporateTrainingBundle\Biz\Teacher\Service;

interface ProfileService
{
    public function createProfile($fields);

    public function updateProfile($id, $fields);

    public function deleteProfile($id);

    public function getProfile($id);

    public function getProfileByUserId($userId);

    public function findProfilesByIds($ids);

    public function findProfilesByLevelId($levelId);

    public function countProfiles($conditions);

    public function searchProfiles($conditions, $orderBy, $start, $limit);
}
