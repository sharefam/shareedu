<?php

namespace CorporateTrainingBundle\Biz\Taxonomy\Service\Impl;

use Biz\Taxonomy\Service\Impl\CategoryServiceImpl as BaseService;
use CorporateTrainingBundle\Biz\Taxonomy\Service\CategoryService;
use AppBundle\Common\ArrayToolkit;

class CategoryServiceImpl extends BaseService implements CategoryService
{
    public function findCategories($groupId)
    {
        $group = $this->getGroup($groupId);

        if (empty($group)) {
            throw $this->createServiceException("Category Group #{$groupId} does not exist");
        }

        return $this->getCategoryDao()->findByGroupId($group['id']);
    }

    public function countCategories($conditions)
    {
        return $this->getCategoryDao()->count($conditions);
    }

    public function initOrgsRelation()
    {
        $fields = array('1', '1.');

        return $this->getCategoryDao()->initOrgsRelation($fields);
    }

    public function searchCategories(array $conditions, array $orderBys, $start, $limit)
    {
        return $this->getCategoryDao()->search($conditions, $orderBys, $start, $limit);
    }

    public function findCategoriesByGroupCodeAndNames($groupCode, array $names)
    {
        if (empty($groupCode) || empty($names)) {
            return array();
        }
        $categoryGroup = $this->getGroupByCode($groupCode);

        if (empty($categoryGroup)) {
            return array();
        }

        return $this->getCategoryDao()->findByGroupIdAndNames($categoryGroup['id'], $names);
    }

    public function updateGroup($id, array $fields)
    {
        $group = $this->getGroup($id);

        if (empty($group)) {
            throw $this->createNotFoundException("Category Group #{$id} does not exist");
        }

        $fields = ArrayToolkit::parts($fields, array('code', 'name', 'depth'));

        $group = $this->getGroupDao()->update($id, $fields);

        return $group;
    }
}
