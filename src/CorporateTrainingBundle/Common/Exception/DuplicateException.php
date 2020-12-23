<?php

namespace CorporateTrainingBundle\Common\Exception;

use AppBundle\Common\Exception\BaseException;

class DuplicateException extends BaseException
{
    public function __construct($message = 'Duplicated', $code = 0, array $headers = array())
    {
        parent::__construct(500, $message, null, $headers, $code);
    }
}
