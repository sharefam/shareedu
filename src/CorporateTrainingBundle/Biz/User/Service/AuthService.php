<?php

namespace CorporateTrainingBundle\Biz\User\Service;

interface AuthService
{
    public function register($registration, $type = 'default');
}
