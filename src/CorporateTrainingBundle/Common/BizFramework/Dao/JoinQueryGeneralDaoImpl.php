<?php

namespace CorporateTrainingBundle\Common\BizFramework\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

abstract class JoinQueryGeneralDaoImpl extends GeneralDaoImpl implements GeneralDaoInterface
{
    /*
     * return 所有join的表名  return array('table1', 'table2', 'table3');
     */
    abstract function getJoinTables();

    public function table()
    {
        $tables = $this->getJoinTables();
        return implode('-', $tables);
    }

    protected function getCacheStrategy()
    {
        return 'tableJoin';
    }

    protected function createQueryBuilder($conditions)
    {
        $conditions = array_filter(
            $conditions,
            function ($value) {
                if ('' === $value || null === $value) {
                    return false;
                }

                if (is_array($value) && empty($value)) {
                    return false;
                }

                return true;
            }
        );

        $builder = $this->getQueryBuilder($conditions);

        $declares = $this->declares();
        $declares['conditions'] = isset($declares['conditions']) ? $declares['conditions'] : array();

        foreach ($declares['conditions'] as $condition) {
            $builder->andWhere($condition);
        }

        return $builder;
    }

}