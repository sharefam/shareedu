<?php

namespace CorporateTrainingBundle\Biz\Focus\Service\Impl;

use Biz\BaseService;
use CorporateTrainingBundle\Biz\Focus\Service\FocusService;

class FocusServiceImpl extends BaseService implements FocusService
{
    public function findFocusAgo($type = 'my', $time)
    {
        $strategyTypes = $this->getStrategyTypes();

        $focusItems = array();
        foreach ($strategyTypes as $strategyType) {
            $focusStrategy = $this->getFocusService($strategyType);

            if (empty($focusStrategy)) {
                continue;
            }

            $focuses = $focusStrategy->findFocusAgo($type, $time);

            if (!empty($focuses)) {
                foreach ($focuses as &$focus) {
                    $focus['type'] = $strategyType;
                    array_push($focusItems, $focus);
                }
            }
        }

        return $this->array_sort($focusItems, 'startTime');
    }

    public function findFocusNow($type = 'my', $time)
    {
        $strategyTypes = $this->getStrategyTypes();

        $focusItems = array();
        foreach ($strategyTypes as $strategyType) {
            $focusStrategy = $this->getFocusService($strategyType);

            if (empty($focusStrategy)) {
                continue;
            }

            $focuses = $focusStrategy->findFocusNow($type, $time);

            if (!empty($focuses)) {
                foreach ($focuses as &$focus) {
                    $focus['type'] = $strategyType;
                    array_push($focusItems, $focus);
                }
            }
        }

        return $this->array_sort($focusItems, 'startTime');
    }

    public function findFocusLater($type = 'my', $time)
    {
        $strategyTypes = $this->getStrategyTypes();

        $focusItems = array();
        foreach ($strategyTypes as $strategyType) {
            $focusStrategy = $this->getFocusService($strategyType);

            if (empty($focusStrategy)) {
                continue;
            }

            $focuses = $focusStrategy->findFocusLater($type, $time);

            if (!empty($focuses)) {
                foreach ($focuses as &$focus) {
                    $focus['type'] = $strategyType;
                    array_push($focusItems, $focus);
                }
            }
        }

        return $this->array_sort($focusItems, 'startTime');
    }

    public function findFocusByStartTimeAndEndTime($type = 'my', $startTime, $endTime)
    {
        $startTime = mktime(0, 0, 0, date('m', $startTime), 1, date('Y', $startTime));
        $endTime = mktime(0, 0, 0, date('m', $endTime), date('d', $endTime) + 1, date('Y', $endTime)) - 1;
        $strategyTypes = $this->getStrategyTypes();
        $focusItems = array();

        foreach ($strategyTypes as $strategyType) {
            $focusStrategy = $this->getFocusService($strategyType);

            if (empty($focusStrategy)) {
                continue;
            }

            $focuses = $focusStrategy->findFocusByStartTimeAndEndTime($type, $startTime, $endTime);

            if (!empty($focuses)) {
                foreach ($focuses as &$focus) {
                    $focus['focus_type'] = $strategyType;

                    array_push($focusItems, $focus);
                }
            }
        }

        return $focusItems;
    }

    protected function array_sort($array, $on, $order = SORT_ASC)
    {
        $new_array = array();
        $sortable_array = array();

        if (count($array) > 0) {
            foreach ($array as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $k2 => $v2) {
                        if ($k2 == $on) {
                            $sortable_array[$k] = $v2;
                        }
                    }
                } else {
                    $sortable_array[$k] = $v;
                }
            }

            if ($order == SORT_ASC) {
                asort($sortable_array);
            } else {
                arsort($sortable_array);
            }

            foreach ($sortable_array as $k => $v) {
                $new_array[$k] = $array[$k];
            }
        }

        return $new_array;
    }

    protected function getStrategyTypes()
    {
        $strategyTypes = array(
            'live_course',
            'project_plan',
            'offline_activity',
        );

        if ($this->biz['pluginConfigurationManager']->isPluginInstalled('Exam')) {
            array_push($strategyTypes, 'exam');
        }

        if ($this->biz['pluginConfigurationManager']->isPluginInstalled('Survey')) {
            array_push($strategyTypes, 'survey');
        }

        return $strategyTypes;
    }

    protected function getFocusService($type)
    {
        $focus = $this->biz->offsetGet('focus_'.$type);
        if (!empty($focus)) {
            return $focus;
        }

        return null;
    }
}
