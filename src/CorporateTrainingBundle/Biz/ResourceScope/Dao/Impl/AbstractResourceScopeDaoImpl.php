<?php

namespace CorporateTrainingBundle\Biz\ResourceScope\Dao\Impl;

use AppBundle\Common\ArrayToolkit;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

abstract class AbstractResourceScopeDaoImpl extends AdvancedDaoImpl
{
    public function findByResourceTypeAndResourceId($resourceType, $resourceId)
    {
        return $this->findByFields(array('resourceType' => $resourceType, 'resourceId' => $resourceId));
    }

    public function countByResourceTypeAndResourceId($resourceType, $resourceId)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table()} WHERE resourceType = ? AND resourceId = ?";

        return $this->db()->fetchColumn($sql, array($resourceType, $resourceId));
    }

    public function findResourceIdsByResourceTypeAndScope($resourceType, $scope)
    {
        $sql = "SELECT `resourceId` FROM {$this->table()} WHERE resourceType = ? AND scope = ?";

        $result = $this->db()->fetchAll($sql, array($resourceType, $scope)) ?: array();

        return ArrayToolkit::column($result, 'resourceId');
    }

    public function findResourceIdsByResourceTypeAndScopes($resourceType, $scopes)
    {
        if (empty($scopes)) {
            return array();
        }

        $marks = str_repeat('?,', count($scopes) - 1).'?';

        $parameters = array_merge(array($resourceType), $scopes);

        $sql = "SELECT DISTINCT(resourceId) FROM {$this->table()} WHERE resourceType = ? AND scope IN ({$marks})";

        $result = $this->db()->fetchAll($sql, $parameters) ?: array();

        return ArrayToolkit::column($result, 'resourceId');
    }

    public function findResourceIdsByResourceTypeAndScopesAndResourceIds($resourceType, $scopes, $resourceIds)
    {
        if (empty($scopes) || empty($resourceIds)) {
            return array();
        }

        $marks = str_repeat('?,', count($scopes) - 1).'?';

        $marks2 = str_repeat('?,', count($resourceIds) - 1).'?';

        $parameters = array_merge(array($resourceType), $scopes, $resourceIds);

        $sql = "SELECT DISTINCT(resourceId) FROM {$this->table()} WHERE resourceType = ? AND scope IN ({$marks}) AND resourceId IN ({$marks2})";

        $result = $this->db()->fetchAll($sql, $parameters) ?: array();

        return ArrayToolkit::column($result, 'resourceId');
    }

    public function findResourceIdsByResourceTypeAndResourceIds($resourceType, $resourceIds)
    {
        if (empty($resourceIds)) {
            return array();
        }

        $marks = str_repeat('?,', count($resourceIds) - 1).'?';

        $parameters = array_merge(array($resourceType), $resourceIds);

        $sql = "SELECT DISTINCT(resourceId) FROM {$this->table()} WHERE resourceType = ? AND resourceId IN ({$marks})";

        $result = $this->db()->fetchAll($sql, $parameters) ?: array();

        return ArrayToolkit::column($result, 'resourceId');
    }

    public function countByResourceTypeAndResourceIdAndScopes($resourceType, $resourceId, $scopes)
    {
        if (empty($scopes)) {
            return 0;
        }

        $marks = str_repeat('?,', count($scopes) - 1).'?';

        $parameters = array_merge(array($resourceType, $resourceId), $scopes);

        $sql = "SELECT COUNT(*) FROM {$this->table()} WHERE resourceType = ? AND resourceId = ? AND scope IN ({$marks})";

        return $this->db()->fetchColumn($sql, $parameters) ?: 0;
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime'),
            'orderbys' => array('id', 'createdTime'),
            'conditions' => array(
                'id IN ( :ids )',
                'resourceId = :resourceId',
                'resourceType = :resourceType',
            ),
        );
    }
}
