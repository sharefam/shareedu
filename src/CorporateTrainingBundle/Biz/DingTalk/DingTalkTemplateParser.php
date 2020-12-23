<?php

namespace CorporateTrainingBundle\Biz\DingTalk;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Exception\InvalidArgumentException;
use Codeages\Biz\Framework\Context\BizAware;

class DingTalkTemplateParser extends BizAware
{
    public function parseTemplate($templateType, $arguments)
    {
        if (!ArrayToolkit::requireds($arguments, array('targetId', 'batch'))) {
            throw new InvalidArgumentException('Lack of required fields');
        }

        if (isset($this->biz['dingtalk_template.'.$templateType])) {
            return $this->biz['dingtalk_template.'.$templateType]->parse($arguments);
        }

        return null;
    }
}
