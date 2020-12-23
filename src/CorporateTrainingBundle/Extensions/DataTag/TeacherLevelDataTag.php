<?php

namespace CorporateTrainingBundle\Extensions\DataTag;

use AppBundle\Extensions\DataTag\DataTag;
use AppBundle\Extensions\DataTag\BaseDataTag;

class TeacherLevelDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取讲师等级.
     *
     * @param array $arguments 参数
     *
     * @return array 讲师等级
     */
    public function getData(array $arguments)
    {
        if (!isset($arguments['levelId'])) {
            throw new \InvalidArgumentException($this->getServiceKernel()->trans('levelId参数缺失'));
        }

        return $this->getLevelService()->getLevel($arguments['levelId']);
    }

    /**
     * @return \CorporateTrainingBundle\Biz\Teacher\Service\Impl\LevelServiceImpl
     */
    protected function getLevelService()
    {
        return $this->getServiceKernel()->getBiz()->service('CorporateTrainingBundle:Teacher:LevelService');
    }
}
