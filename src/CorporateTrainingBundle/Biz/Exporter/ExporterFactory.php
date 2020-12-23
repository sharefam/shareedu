<?php

namespace CorporateTrainingBundle\Biz\Exporter;

use AppBundle\Common\Exception\InvalidArgumentException;
use Codeages\Biz\Framework\Context\BizAware;

class ExporterFactory extends BizAware
{
    public function create($exportType)
    {
        $exportType = 'export.'.$exportType;
        if (!isset($this->biz[$exportType])) {
            throw new InvalidArgumentException(sprintf('Unknown export type %s', $exportType));
        }

        return $this->biz[$exportType];
    }
}
