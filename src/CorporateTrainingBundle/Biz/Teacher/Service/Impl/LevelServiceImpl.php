<?php

namespace CorporateTrainingBundle\Biz\Teacher\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use CorporateTrainingBundle\Biz\Teacher\Service\LevelService;

class LevelServiceImpl extends BaseService implements LevelService
{
    public function createLevel($fields)
    {
        $this->validateLevelFields($fields);
        if ($this->getLevelByName($fields['name'])) {
            throw $this->createServiceException('Level already exists');
        }
        $fields = $this->filterLevelFields($fields);

        return $this->getLevelDao()->create($fields);
    }

    public function updateLevel($id, $fields)
    {
        $this->checkLevelExist($id);

        return $this->getLevelDao()->update($id, $fields);
    }

    public function deleteLevel($id)
    {
        $this->checkLevelExist($id);

        return  $this->getLevelDao()->delete($id);
    }

    public function getLevel($id)
    {
        return $this->getLevelDao()->get($id);
    }

    public function getLevelByName($name)
    {
        return $this->getLevelDao()->getByName($name);
    }

    public function findLevelsByIds($ids)
    {
        return $this->getLevelDao()->findByIds($ids);
    }

    public function findAllLevels()
    {
        return $this->getLevelDao()->search(array(), array(), 0, PHP_INT_MAX);
    }

    public function countLevels($conditions)
    {
        return $this->getLevelDao()->count($conditions);
    }

    public function searchLevels($conditions, $orderBy, $start, $limit)
    {
        return $this->getLevelDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function isLevelNameAvailable($name, $exclude)
    {
        $level = $this->getLevelDao()->getByName($name);

        if (empty($level)) {
            return true;
        }

        return $level['name'] === $exclude;
    }

    protected function validateLevelFields($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('name'), true)) {
            throw $this->createInvalidArgumentException('Lack of required fields');
        }
    }

    protected function filterLevelFields($fields)
    {
        return ArrayToolkit::parts($fields, array('name', 'createdUserId'));
    }

    protected function checkLevelExist($id)
    {
        $level = $this->getLevel($id);

        if (empty($level)) {
            throw $this->createNotFoundException("Level#{$id} Not Exist");
        }
    }

    /**
     * @return \CorporateTrainingBundle\Biz\Teacher\Dao\Impl\LevelDaoImpl
     */
    protected function getLevelDao()
    {
        return $this->createDao('CorporateTrainingBundle:Teacher:LevelDao');
    }
}
