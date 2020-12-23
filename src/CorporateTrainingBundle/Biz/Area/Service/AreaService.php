<?php

namespace CorporateTrainingBundle\Biz\Area\Service;

interface AreaService
{
    public function findAllProvinceCodes();

    public function findAreasByParentId($parentId);

    public function getArea($id);

    public function createArea($fields);

    public function searchAreas($conditions, $orderBy, $start, $limit);
}
