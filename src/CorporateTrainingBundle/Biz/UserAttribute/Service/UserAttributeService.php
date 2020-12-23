<?php

namespace CorporateTrainingBundle\Biz\UserAttribute\Service;

interface UserAttributeService
{
    public function findDistinctUserIdsByAttributes(array $attributes, $orgIds = array());

    public function searchAttributesName(array $attributes, $likeName, $orgIds = array());
}
