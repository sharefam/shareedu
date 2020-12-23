<?php

namespace CorporateTrainingBundle\Extensions\DataTag;

use AppBundle\Extensions\DataTag\DataTag;
use AppBundle\Extensions\DataTag\BaseDataTag;

class ProjectPlanCategoriesDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取培训项目分类下有可报名或报名未开始培训项目的分类.
     *
     * @param array $arguments 参数
     *
     * @return array 项目分类
     */
    public function getData(array $arguments)
    {
        $group = $this->getCategoryService()->getGroupByCode('projectPlan');
        if (empty($group)) {
            throw new \InvalidArgumentException($this->getServiceKernel()->trans('group:%argumentsGroup%不存在', array('%argumentsGroup%' => 'projectPlan')));
        }

        $categories = $this->getCategoryService()->findCategories($group['id']);
        foreach ($categories as $key => &$category) {
            $projectPlans = $this->getProjectPlanService()->searchProjectPlans(
                array('requireEnrollment' => 1, 'categoryId' => $category['id'], 'status' => 'published'),
                array(),
                0,
                1
            );
            if (empty($projectPlans)) {
                unset($categories[$key]);
            }
        }
        $categories = array_merge(array(), $categories);
        if (!empty($arguments['count'])) {
            if (count($categories) > $arguments['count']) {
                $categories = array_slice($categories, 0, $arguments['count']);
            }
        }

        return $categories;
    }

    protected function getProjectPlanService()
    {
        return $this->getServiceKernel()->getBiz()->service('CorporateTrainingBundle:ProjectPlan:ProjectPlanService');
    }

    protected function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy:CategoryService');
    }
}
