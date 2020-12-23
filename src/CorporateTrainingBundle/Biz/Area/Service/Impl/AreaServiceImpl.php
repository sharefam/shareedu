<?php

namespace CorporateTrainingBundle\Biz\Area\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use CorporateTrainingBundle\Biz\Area\Dao\AreaDao;
use CorporateTrainingBundle\Biz\Area\Service\AreaService;

class AreaServiceImpl extends BaseService implements AreaService
{
    public function findAllProvinceCodes()
    {
        $provinces = $this->getAreaDao()->findByParentId(0);
        $provinceCodes = array();
        foreach ($provinces as $index => $province) {
            $provinceCodes[$province['id']] = $province['name'];
        }

        return $provinceCodes;
    }

    public function findAreasByParentId($parentId)
    {
        return $this->getAreaDao()->findByParentId($parentId);
    }

    public function getArea($id)
    {
        return $this->getAreaDao()->get($id);
    }

    public function createArea($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('name'))) {
            throw $this->createInvalidArgumentException('lack of fields');
        }

        $fields = $this->filterFields($fields);

        return $this->getAreaDao()->create($fields);
    }

    public function searchAreas($conditions, $orderBy, $start, $limit)
    {
        return $this->getAreaDao()->search($conditions, $orderBy, $start, $limit);
    }

    protected function filterFields($fields)
    {
        return ArrayToolkit::parts($fields, array(
            'name',
            'parentId',
        ));
    }

    /**
     * @return AreaDao
     */
    protected function getAreaDao()
    {
        return $this->createDao('CorporateTrainingBundle:Area:AreaDao');
    }
}
