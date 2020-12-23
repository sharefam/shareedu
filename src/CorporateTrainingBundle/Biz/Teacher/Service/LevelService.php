<?php

namespace CorporateTrainingBundle\Biz\Teacher\Service;

interface LevelService
{
    public function createLevel($fields);

    public function updateLevel($id, $fields);

    public function deleteLevel($id);

    public function getLevel($id);

    public function getLevelByName($name);

    public function findLevelsByIds($ids);

    public function findAllLevels();

    public function countLevels($conditions);

    public function searchLevels($conditions, $orderBy, $start, $limit);

    public function isLevelNameAvailable($name, $exclude);
}
