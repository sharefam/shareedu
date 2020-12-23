<?php

namespace CorporateTrainingBundle\Biz\Org\Dao\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\Org\Dao\Impl\OrgDaoImpl as BaseDaoImpl;

class OrgDaoImpl extends BaseDaoImpl
{
    public function deleteOrgsWithoutOrgCode($orgCode)
    {
        $sql = "DELETE FROM {$this->table} WHERE orgCode <> ?";

        return $this->db()->executeUpdate($sql, array($orgCode));
    }

    public function getOrgBySyncId($syncId)
    {
        return $this->getByFields(array('syncId' => $syncId));
    }

    public function findOrgsBySyncIds($syncIds)
    {
        return $this->findInField('syncId', $syncIds);
    }

    public function declares()
    {
        $declares = parent::declares();

        array_push($declares['orderbys'], 'parentId');
        array_push($declares['orderbys'], 'depth');

        array_push($declares['conditions'], 'id IN ( :orgIds)');
        array_push($declares['conditions'], 'orgCode IN ( :orgCodes)');
        array_push($declares['conditions'], 'name LIKE :name');
        array_push($declares['conditions'], 'orgCode LIKE :likeOrgCode');
        array_push($declares['conditions'], 'code = :code');

        return $declares;
    }

    public function findByPrefixOrgCodes(array $orgCodes, $columns = array())
    {
        $columns = ArrayToolkit::parts($columns, array('id', 'orgCode', 'code', 'name'));
        if (empty($columns)) {
            $sql = "SELECT * FROM {$this->table()} WHERE orgCode LIKE ?";
        } else {
            $columns = implode(',', $columns);
            $sql = 'SELECT '.$columns." FROM {$this->table()} WHERE orgCode LIKE ?";
        }
        $query = array(current($orgCodes).'%');

        if (1 == count($orgCodes)) {
            $sql .= ' ORDER BY orgCode';
            $query = array(current($orgCodes).'%');
        } else {
            array_shift($orgCodes);
            foreach ($orgCodes as $orgCode) {
                $sql .= ' OR orgCode Like ?';
                array_push($query, $orgCode.'%');
            }

            $sql .= ' ORDER BY orgCode';
        }

        return $this->db()->fetchAll($sql, $query) ?: array();
    }

    public function findByOrgCodes(array $orgCodes)
    {
        return $this->findInField('orgCode', $orgCodes);
    }

    public function findByCodes(array $codes)
    {
        return $this->findInField('code', $codes);
    }

    public function findByParentOrgId($orgId)
    {
        return $this->findByFields(array('parentId' => $orgId));
    }
}
