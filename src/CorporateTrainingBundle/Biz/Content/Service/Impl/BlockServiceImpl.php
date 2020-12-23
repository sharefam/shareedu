<?php

namespace CorporateTrainingBundle\Biz\Content\Service\Impl;

use Biz\Content\Service\Impl\BlockServiceImpl as BaseService;
use AppBundle\Common\ArrayToolkit;
use CorporateTrainingBundle\Biz\Content\Service\BlockService;

class BlockServiceImpl extends BaseService implements BlockService
{
    public function createBlock($block)
    {
        if (!ArrayToolkit::requireds($block, array('code', 'data', 'content', 'blockTemplateId'))) {
            throw $this->createInvalidArgumentException('Lack of required fields');
        }
        $user = $this->getCurrentUser();
        $block['createdTime'] = time();
        $block['updateTime'] = time();
        $block['userId'] = $user['id'];
        unset($block['mode']);
        $createdBlock = $this->getBlockDao()->create($block);

        $blockHistoryInfo = array(
            'blockId' => $createdBlock['id'],
            'content' => $createdBlock['content'],
            'userId' => $createdBlock['userId'],
            'createdTime' => time(),
        );

        $this->getBlockHistoryDao()->create($blockHistoryInfo);

        $blockTemplate = $this->getBlockTemplateDao()->get($createdBlock['blockTemplateId']);
        $createdBlock['id'] = $blockTemplate['id'];
        $createdBlock['title'] = $blockTemplate['title'];
        $createdBlock['mode'] = $blockTemplate['mode'];

        return $createdBlock;
    }

    public function getBlockByCode($code)
    {
        $result = $this->getBlockDao()->getByCode($code);
        if (empty($result)) {
            $blockTemplate = $this->getBlockTemplateByCode($code);
            $blockTemplate['blockTemplateId'] = !empty($blockTemplate) ? $blockTemplate['id'] : 0;
            $blockTemplate['blockId'] = 0;

            return $blockTemplate;
        } else {
            $blockTemplate = $this->getBlockTemplate($result['blockTemplateId']);
            $result['meta'] = $blockTemplate['meta'];
            $result['mode'] = $blockTemplate['mode'];
            $result['templateName'] = $blockTemplate['templateName'];
            $result['blockId'] = $result['id'];
            $result['blockTemplateId'] = $blockTemplate['id'];

            return $result;
        }
    }

    public function getBlocksByBlockTemplateIds($blockTemplateIds)
    {
        $blocks = array();
        foreach ($blockTemplateIds as $blockTemplateId) {
            $blocks[] = $this->getBlockDao()->getByTemplateId($blockTemplateId);
        }

        return $blocks;
    }

    public function getBlockByTemplateId($blockTemplateId)
    {
        $block = $this->getBlockDao()->getByTemplateId($blockTemplateId);
        if (empty($block)) {
            $blockTemplate = $this->getBlockTemplate($blockTemplateId);
            $blockTemplate['blockTemplateId'] = $blockTemplate['id'];
            $blockTemplate['blockId'] = 0;

            return $blockTemplate;
        }
        $blockTemplate = $this->getBlockTemplate($blockTemplateId);
        $block['blockId'] = $block['id'];
        $block['blockTemplateId'] = $blockTemplate['id'];
        $block['code'] = $blockTemplate['code'];
        $block['template'] = $blockTemplate['template'];
        $block['tips'] = $blockTemplate['tips'];
        $block['mode'] = $blockTemplate['mode'];
        $block['meta'] = $blockTemplate['meta'];
        $block['title'] = $blockTemplate['title'];
        $block['templateName'] = $blockTemplate['templateName'];

        return $block;
    }
}
