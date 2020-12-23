<?php

namespace CorporateTrainingBundle\Common;

use AppBundle\Common\ArrayToolkit;

class OrgTreeToolkit
{
    /**
     * [maketree description].
     *
     * @param array $data 需要组建的组织机构数据
     *
     * @return [array] tree data 树状结构
     */
    public static function makeTree(array $data)
    {
        $groupedDataByDepth = self::groupByDepth($data);

        $tree = self::makeTreeRecursively($groupedDataByDepth, 0, 0);

        return $tree;
    }

    private static function groupByDepth($data)
    {
        $groupByDepth = array();
        foreach ($data as $org) {
            if (!isset($groupByDepth[$org['depth']])) {
                $groupByDepth[$org['depth']] = array();
            }
            $groupByDepth[$org['depth']][] = $org;
        }

        return $groupByDepth;
    }

    private static function makeTreeRecursively(&$groupedDataByDepth, $parentId, $parentDepth)
    {
        $tree = self::makeParentTree($groupedDataByDepth, $parentId, $parentDepth);

        foreach ($tree as $key => $value) {
            $tree[$key]['nodes'] = self::makeTreeRecursively($groupedDataByDepth, $value['id'], $value['depth']);
        }

        return $tree;
    }

    private static function makeParentTree(&$groupedDataByDepth, $parentId, $parentDepth)
    {
        $filtered = array();
        if (isset($groupedDataByDepth[$parentDepth + 1])) {
            foreach ($groupedDataByDepth[$parentDepth + 1] as $key => $value) {
                if ($value['parentId'] == $parentId) {
                    $filtered[] = $value;
                    unset($groupedDataByDepth[$parentDepth + 1][$key]);
                }
            }

            $sortArray = ArrayToolkit::column($filtered, 'seq');

            array_multisort($sortArray, $filtered);
        }

        return $filtered;
    }
}
