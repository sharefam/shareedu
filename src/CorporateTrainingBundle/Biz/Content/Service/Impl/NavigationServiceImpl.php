<?php

namespace CorporateTrainingBundle\Biz\Content\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\Content\Service\Impl\NavigationServiceImpl as BaseService;
use CorporateTrainingBundle\Biz\Content\Service\NavigationService;

class NavigationServiceImpl extends BaseService implements NavigationService
{
    /**
     * 内训不过滤组织机构
     */
    public function searchNavigationCount($conditions)
    {
        return $this->getNavigationDao()->count($conditions);
    }

    /**
     *内训不过滤组织机构
     */
    public function searchNavigations($conditions, $orderBy, $start, $limit)
    {
        return $this->getNavigationDao()->search($conditions, $orderBy, $start, $limit);
    }

    /**
     * 内训导航不过滤组织机构
     */
    public function getOpenedNavigationsTreeByType($type)
    {
        $conditions = array(
            'type' => $type,
            'isOpen' => 1,
        );

        $count = $this->searchNavigationCount($conditions);
        if ($count == 0) {
            return array();
        }

        $navigations = $this->searchNavigations(
            $conditions,
            array('sequence' => 'ASC'),
            0,
            $count
        );

        $navigations = ArrayToolkit::index($navigations, 'id');

        foreach ($navigations as $index => $nav) {
            //只显示Open菜单
            // if (empty($nav['isOpen']) || $nav['isOpen'] != 1) {
            //     unset($navigations[$index]);
            //     continue;
            // }

            //一级菜单 - 保留
            if ($nav['parentId'] == 0) {
                continue;
            }

            //二级菜单

            //如果父菜单不存在(被删除)，子菜单不显示
            if (!isset($navigations[$nav['parentId']])) {
                unset($navigations[$index]);
                continue;
            }

            //如果父菜单是close的，子菜单不显示
            $parent = $navigations[$nav['parentId']];

            if ((empty($parent['isOpen']) || $parent['isOpen'] != 1)) {
                unset($navigations[$index]);
                continue;
            }

            //初始化父菜单的children数组
            if (empty($navigations[$nav['parentId']]['children'])) {
                $navigations[$nav['parentId']]['children'] = array();
            }

            //子菜单是open的，放到父菜单中
            if ($nav['isOpen']) {
                $navigations[$nav['parentId']]['children'][] = $nav;
                unset($navigations[$index]);
            }
        }

        return $navigations;
    }

    public function initOrgsRelation()
    {
        $fields = array('1', '1.');

        return $this->getNavigationDao()->initOrgsRelation($fields);
    }
}
