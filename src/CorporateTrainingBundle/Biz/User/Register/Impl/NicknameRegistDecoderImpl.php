<?php

namespace CorporateTrainingBundle\Biz\User\Register\Impl;

use Biz\User\Register\Impl\RegistDecoder;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use AppBundle\Common\SimpleValidator;

class NicknameRegistDecoderImpl extends RegistDecoder
{
    protected function validateBeforeSave($registration)
    {
        if (empty($registration['nickname']) || !SimpleValidator::nickname($registration['nickname'])) {
            throw new InvalidArgumentException('Invalid Nickname');
        }

        if (!$this->getUserService()->isNicknameAvaliable($registration['nickname'])) {
            throw new InvalidArgumentException('Nickname Occupied');
        }
    }
}
