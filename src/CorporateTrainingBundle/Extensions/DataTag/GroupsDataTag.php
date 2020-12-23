<?php

namespace CorporateTrainingBundle\Extensions\DataTag;

use AppBundle\Extensions\DataTag\DataTag;
use AppBundle\Extensions\DataTag\BaseDataTag;

class GroupsDataTag extends BaseDataTag implements DataTag
{
    /**
     * 首页获取培训项目.
     *
     * @param array $arguments 参数
     *
     * @return array 小组
     */
    public function getData(array $arguments)
    {
        $conditions = array();
        if (!empty($arguments['keywords'])) {
            $conditions['title'] = $arguments['keywords'];
        }
        if (empty($arguments['count'])) {
            $arguments['count'] = PHP_INT_MAX;
        }
        $groups = $this->getGroupService()->searchGroups($conditions, array('memberNum' => 'DESC'), 0, $arguments['count']);

        return $groups;
    }

    /**
     * @return \Biz\Group\Service\Impl\GroupServiceImpl
     */
    protected function getGroupService()
    {
        return $this->getServiceKernel()->createService('Group:GroupService');
    }
}
