<?php

namespace CorporateTrainingBundle\Biz\ResourceScope\Strategy;

interface ResourceScopeStrategy
{
    /**
     * 判断用户是否在发布范围内
     *
     * @param string $resourceType 资源类型
     * @param string $resourceId   资源ID
     * @param string $userId       用户ID
     *
     * @return bool
     */
    public function canVisit($resourceType, $resourceId, $userId);

    /**
     * 判断用户是否在加入范围内
     *
     * @param string $resourceType 资源类型
     * @param string $resourceId   资源ID
     * @param string $userId       用户ID
     *
     * @return bool
     */
    public function canAccess($resourceType, $resourceId, $userId);

    /**
     * 设置发布范围
     *
     * @param string       $resourceType 资源类型
     * @param string       $resourceId   资源ID
     * @param string|array $data         发布范围Id
     *
     * @return
     */
    public function setResourceVisibleScope($resourceType, $resourceId, $data);

    /**
     * 设置加入范围
     *
     * @param string $resourceType 资源类型
     * @param string $resourceId   资源ID
     * @param array  $data         加入范围Id
     *
     * @return
     */
    public function setResourceAccessScope($resourceType, $resourceId, $data);

    /**
     * 查找用户可见的资源id
     *
     * @param string $resourceType 资源类型
     * @param string $userId       用户ID
     * @param array  $preResultIds 前置查询结果
     *
     * @return array
     */
    public function findVisibleResourceIds($resourceType, $userId, $preResultIds = array());

    /**
     * 查找资源的发布范围
     *
     * @param $resourceType
     * @param $resourceId
     *
     * @return array
     */
    public function findVisibleScopesByResourceTypeAndResourceId($resourceType, $resourceId);

    /**
     * 查找资源的加入范围
     *
     * @param $resourceType
     * @param $resourceId
     *
     * @return array
     */
    public function findAccessScopesByResourceTypeAndResourceId($resourceType, $resourceId);
}
