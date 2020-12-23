<?php

namespace CorporateTrainingBundle\Biz\DingTalk\Template;

interface DingTalkTemplateInterface
{
    /**
     * @param $options
     *
     * @return array
     */
    public function parse($options);
}
