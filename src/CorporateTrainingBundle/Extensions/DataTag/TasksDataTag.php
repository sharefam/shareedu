<?php

namespace CorporateTrainingBundle\Extensions\DataTag;

use CorporateTrainingBundle\Extensions\DataTag\TaskBaseDataTag as BaseDataTag;
use AppBundle\Extensions\DataTag\DataTag;

class TasksDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取Task列表.
     *
     * 可传入的参数：
     *   title    可选  任务名称
     *   categoryId  可选  分类名称
     *   courseSetId  可选 课程ID
     *   count    必需 课程数量，取值不能超过100
     *
     * @param array $arguments 参数
     *
     * @return array 课程列表
     */
    public function getData(array $arguments)
    {
        if (empty($arguments['count'])) {
            $arguments['count'] = 1;
        }
        $conditions = $this->getConditions($arguments);

        return $this->getTaskService()->searchTasks($conditions, array('createdTime' => 'DESC'), 0, $arguments['count']);
    }

    protected function getConditions($arguments)
    {
        $conditions = array();
        $conditions['status'] = 'published';

        if (isset($arguments['title'])) {
            $conditions['titleLike'] = $arguments['title'];
        }
        if (isset($arguments['courseSetId'])) {
            $conditions['fromCourseSetId'] = $arguments['courseSetId'];
        }
        if (isset($arguments['categoryId'])) {
            $conditions['categoryId'] = $arguments['categoryId'];
        }

        return $conditions;
    }
}
