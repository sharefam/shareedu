<?php

namespace CorporateTrainingBundle\Extensions\DataTag;

use AppBundle\Extensions\DataTag\BaseDataTag;
use AppBundle\Extensions\DataTag\DataTag;

class BlockDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取所有Blocks.
     *
     * 可传入的参数：
     *
     *   code Block编码
     *
     * @param array $arguments 参数
     *
     * @return array Block
     */
    public function getData(array $arguments)
    {
        if (empty($arguments['code'])) {
            return array();
        }

        return $this->getBlockService()->getBlockByCode($arguments['code']);
    }

    protected function getBlockService()
    {
        return $this->getServiceKernel()->createService('CorporateTrainingBundle:Content:BlockService');
    }
}
