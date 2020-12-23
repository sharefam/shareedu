<?php

namespace CorporateTrainingBundle\Biz\Content\Service;

use Biz\Content\Service\BlockService as BaseService;

interface BlockService extends BaseService
{
    public function getBlocksByBlockTemplateIds($blockTemplateIds);

    public function getBlockByTemplateId($blockTemplateId);
}
