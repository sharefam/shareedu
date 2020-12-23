<?php

namespace CorporateTrainingBundle\Biz\Taxonomy\Service\Impl;

use Biz\Taxonomy\Service\Impl\TagServiceImpl as BaseService;

class TagServiceImpl extends BaseService
{
    public function searchTags($conditions, $sort, $start, $limit)
    {
        return $this->getTagDao()->search($conditions, $sort, $start, $limit);
    }

    public function searchTagCount($conditions)
    {
        return $this->getTagDao()->count($conditions);
    }

    public function initOrgsRelation()
    {
        $fields = array('1', '1.');

        return $this->getTagDao()->initOrgsRelation($fields);
    }
}
